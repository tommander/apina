<?php

declare(strict_types=1);

namespace Apina\Helpers;

use Apina\Application;
use Apina\Messages\Message;
use Apina\Messages\Request;
use Apina\Messages\Response;
use Apina\Storages\Storage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * @psalm-type ApinaResponseData = array{code: int, data: mixed}
 */
final class ApinaHelper
{
    private Application $app;

    public function __construct(private Storage $storage, LoggerInterface|null $logger = null)
    {
        $this->app = new Application($storage);
        $logger && $this->app->setLogger($logger);
    }

    /**
     * Calls Apina
     *
     * @param string $method HTTP Request Method - GET, POST, ...
     * @param string $url URL relative to the API endpoint, e.g. home '/', list '/type', resource '/type/id'
     * @param mixed $body Data to send with the request (will be encoded as JSON)
     * @return ApinaResponseData
     */
    private function doCall(string $method, string $url, mixed $data = null): array
    {
        $request = new Request('', '', '', 0, '', '', [], [], []);
        $request->__unserialize(['verb' => $method, 'object' => $url, 'data' => $data]);

        $response = $this->app->processRequest($request)->__serialize();
        $code = 418;
        if (isset($response['code']) && is_int($response['code'])) {
            $code = $response['code'];
        }
        $data = [];
        if (isset($response['data']) && is_array($response['data'])) {
            $data = $response['data'];
        }
        return ['code' => $code, 'data' => $data];
    }

    /**
     * Register new resource type
     *
     * Input data: {string: {source: string, type: string, required?: bool, key?: string}}
     *
     * @param string $name
     * @param mixed $data
     * @return ApinaResponseData
     */
    public function registerResource(string $name, mixed $data): array
    {
        return $this->doCall('PUT', "/resource/{$name}", $data);
    }

    /**
     * Get API base info
     * @return ApinaResponseData
     */
    public function home(): array
    {
        return $this->doCall('GET', '/');
    }

    /**
     * Get resource data
     * @param string $type Registered resource type
     * @param string $id Resource ID
     * @return ApinaResponseData
     */
    public function getResource(string $type, string $id): array
    {
        return $this->doCall('GET', "/{$type}/{$id}");
    }

    /**
     * List all resources of the given type
     * @param string $type Registered resource type
     * @return ApinaResponseData
     */
    public function listResources(string $type): array
    {
        return $this->doCall('GET', "/{$type}");
    }

    /**
     * Check that a resource exists
     * @param string $type Registered resource type
     * @param string $id Resource ID
     * @return ApinaResponseData
     */
    public function hasResource(string $type, string $id): array
    {
        return $this->doCall('HEAD', "/{$type}/{$id}");
    }

    /**
     * Check that a resource type exists
     * @param string $type Registered resource type
     * @return ApinaResponseData
     */
    public function hasResourceType(string $type): array
    {
        return $this->doCall('HEAD', "/{$type}");
    }

    /**
     * Create new or overwrite existing resource.
     * @param string type Registered resource type
     * @param any data Resource data (all required properties must be included)
     * @param string|null id Resource ID (will be generated automatically if id is null)
     * @return ApinaResponseData
     */
    public function createResource(string $type, mixed $data, string|null $id = null): array
    {
        return $this->doCall('PUT', ($id === null) ? "/{$type}" : "/{$type}/{$id}", $data);
    }

    /**
     * Change existing object.
     *
     * Note: if the data includes all required fields, it behaves the same as apiNewObject
     * @param string type Registered resource type
     * @param any data New resource data (required fields don't have to be included)
     * @param string id Resource ID
     * @return ApinaResponseData
     */
    public function changeResource(string $type, mixed $data, string|null $id = null): array
    {
        return $this->doCall('POST', ($id === null) ? "/{$type}" : "/{$type}/{$id}", $data);
    }

    /**
     * Delete specific resource.
     * @param string type Registered resource type
     * @param string id Resource ID
     * @return ApinaResponseData
     */
    public function deleteResource(string $type, string $id): array
    {
        return $this->doCall('DELETE', "/{$type}/{$id}");
    }

    /**
     * Delete all resources with the given type.
     *
     * @param string type Registered resource type
     * @return ApinaResponseData
     */
    public function deleteResourceType(string $type): array
    {
        return $this->doCall('DELETE', "/{$type}");
    }

    /**
     * Delete everything.
     *
     * @return ApinaResponseData
     */
    public function deleteEverything(): array
    {
        return $this->doCall('DELETE', '/');
    }
}
