<?php

declare(strict_types=1);

namespace Apina;

use Apina\Helpers\ApiHelper;
use Apina\Messages\Request;
use Apina\Storages\Storage;
use Psr\Log\LoggerAwareTrait;
use Apina\Messages\Response;

final class Application
{
    use LoggerAwareTrait;

    public function __construct(
        private Storage $storage,
    ) {
    }

    public function processRequest(Request $request): Response
    {
        $responseId = bin2hex(random_bytes(8));
        $responseCode = 418;
        $responseData = [];

        if ($request->object === '/' && ($request->verb === 'GET' || $request->verb === 'HEAD')) {
            $list = [];
            $objects = $this->storage->listObjects();
            foreach ($objects as $href) {
                $parts = Resource::explodeHref($href);
                if ($parts['type'] !== 'resource') {
                    continue;
                }
                $list[$parts['id']] = ['href' => '/' . $parts['id']];
            }
            $responseCode = 200;
            $responseData = ['_links' => $list];
        } elseif ($request->object === '/' && $request->verb === 'DELETE') {
            $list = [];
            $objects = $this->storage->listObjects();
            foreach ($objects as $href) {
                $this->storage->deleteObject($href);
            }
            $responseCode = 200;
            $responseData = [];
        } else {
            $resource = Resource::newFromHref($this->storage, $request->object);
            ($this->logger !== null) && $resource->setLogger($this->logger);
            $representation = $this->applyVerb($resource, $request);
            $responseCode = $representation['code'];
            $responseData = $representation['data'];
        }

        $response = new Response($responseId, $request->recipient, $request->sender, time(), $request->object, $request->verb, $responseData, $responseCode, $request->id);
        ($this->logger) && $this->logger->debug("Request:\n{request}\nResponse:\n{response}\n\n", ['request' => $request->serialize(), 'response' => $response->serialize()]);
        return $response;
    }

    /** @return array{code: int<100,599>, data: array} */
    private function applyVerb(Resource $resource, Request $request): array
    {
        ($this->logger) && $this->logger->debug("Resource:\n{resource}\n", ['resource' => $resource->serialize()]);
        $hasId = ($resource->id !== '');

        // GET /object/id
        // HEAD /object/id
        if ($hasId && ($request->verb === 'GET' || $request->verb === 'HEAD')) {
            $typeObj = $this->storage->getObject('/resource/' . $resource->type);
            if ($typeObj === null) {
                return ['data' => ['error' => ['message' => 'Unknown resource type "' . $resource->type . '"']], 'code' => 404];
            }
            if (!$resource->valid()) {
                return ['data' => ['error' => ['message' => 'Resource "' . $resource->href() . '" does not exist']], 'code' => 404];
            }
            return ['data' => ApiHelper::halResourceObject($resource->__serialize(), ['self' => ['href' => $resource->href()]], []), 'code' => 200];
        }

        // GET /object
        // HEAD /object
        if (!$hasId && ($request->verb === 'GET' || $request->verb === 'HEAD')) {
            $typeObj = $this->storage->getObject('/resource/' . $resource->type);
            if ($typeObj === null) {
                return ['data' => ['error' => ['message' => 'Unknown resource type "' . $resource->type . '"']], 'code' => 404];
            }
            $list = $this->storage->listObjects();
            $ret = [];
            foreach ($list as $href) {
                $res = Resource::explodeHref($href);
                if ($res['type'] !== $resource->type) {
                    continue;
                }
                $ret[] = $href;
            }
            return ['data' => $ret, 'code' => 200];
        }

        // POST /object
        // POST /object/id
        // PUT /object
        // PUT /object/id
        if ($request->verb === 'POST' || $request->verb === 'PUT') {
            $oldValue = $resource->fullDataForUnserialize;
            $resource->fullDataForUnserialize = ($request->verb === 'PUT');
            ($this->logger) && $this->logger->debug('PEEKABOO ' . ($resource->fullDataForUnserialize ? 'PUT' : 'NOPUT'));
            $resource->__unserialize($request->data);
            $resource->fullDataForUnserialize = $oldValue;
            if ($resource->lastError === false) {
                return ['data' => ApiHelper::halResourceObject($resource->__serialize(), ['self' => ['href' => $resource->href()]], []), 'code' => 200];
            }
            return ['data' => ['error' => ['message' => $resource->lastError]], 'code' => 400];
        }

        // DELETE /object/id
        if ($hasId && $request->verb === 'DELETE') {
            $result = $resource->removeFromStorage();
            return ['data' => $result ? [] : ['error' => ['message' => 'Cannot remove object from storage.']], 'code' => $result ? 200 : 500];
        }

        // DELETE /object
        if (!$hasId && $request->verb === 'DELETE') {
            $list = $this->storage->listObjects();
            $log = [];
            foreach ($list as $obj) {
                if (!str_starts_with($obj, "{$request->object}/")) {
                    continue;
                }
                $log[] = $obj;
                $this->storage->deleteObject($obj);
            }
            $log[] = "/resource{$request->object}";
            $this->storage->deleteObject("/resource{$request->object}");

            return ['data' => $log, 'code' => 200];
        }

        return ['data' => ['error' => ['message' => "Unrecognized verb " . ($hasId ? 'single' : 'multi') . " " . $request->verb]], 'code' => 501];
    }
}
