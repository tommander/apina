<?php

declare(strict_types=1);

use Apina\Helpers\ApiHelper;
use Apina\Helpers\Helper;
use Apina\Loggers\FileLogger;
use Apina\Application;
use Apina\Storages\JsonStorage;
use Apina\Messages\Request;

require_once __DIR__ . '/../vendor/autoload.php';

if (
    !defined('APINA_URLPATH_PREFIX') ||
    !defined('APINA_LOG_PATH') ||
    !defined('APINA_STORAGE_PATH')
) {
    die('Potato');
}

/** @psalm-suppress MixedArgument */
$httpRequest = ApiHelper::parseRequest(APINA_URLPATH_PREFIX);
$request = new Request(
    Helper::anyToStr($_REQUEST['id'] ?? null),
    Helper::anyToStr($_SERVER['REMOTE_ADDR'] ?? ''),
    Helper::anyToStr($_SERVER['SERVER_ADDR'] ?? ''),
    Helper::anyToInt($_SERVER['REQUEST_TIME'] ?? 0),
    Helper::anyToStr($httpRequest['uri']['path']),
    Helper::anyToNonEmptyStr($_SERVER['REQUEST_METHOD'] ?? 'NONE', 'NONE'),
    is_array($httpRequest['content']) ? $httpRequest['content'] : [],
    [],
    [],
);

/** @psalm-suppress MixedArgument */
$logger = new FileLogger(APINA_LOG_PATH);
/** @psalm-suppress MixedArgument */
$storage = new JsonStorage(APINA_STORAGE_PATH);
$storage->setLogger($logger);
$app = new Application($storage);
$response = $app->processRequest($request);

ApiHelper::sendResponse([
    'code' => $response->code,
    'contentType' => 'application/json',
    'headers' => [],
    'method' => Helper::anyToNonEmptyStr($_SERVER['REQUEST_METHOD'] ?? 'NONE', 'NONE'),
    'body' => $response->data,
]);
