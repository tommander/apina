<?php

declare(strict_types=1);

namespace Apina\Tests;

use Apina\Loggers\MemoryLogger;
use Apina\Loggers\FileLogger;
use Apina\Messages\Request;
use Apina\Application;
use Apina\Storages\JsonStorage;
use Apina\Storages\MemoryStorage;
use Apina\Storages\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @psalm-type TestJsonItem = array{title: string, act: array{method: string, url: string, data?: array}, exp: array{method: string, url: string, code: int, data?: array}}
 */
final class AlboomTest extends TestCase
{
    private static LoggerInterface|null $logger = null;
    private static Storage|null $storage = null;
    private static Application|null $app = null;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$logger = new MemoryLogger();
        // self::$logger = new FileLogger(__DIR__ . '/../../../log_phpunit.txt');

        self::$storage = new MemoryStorage();
        // self::$storage = new JsonStorage(__DIR__ . '/../../../_storage_phpunit.json');
        self::$storage->setLogger(self::$logger);

        self::$app = new Application(self::$storage);
        self::$app->setLogger(self::$logger);
    }

    #[\Override]
    protected function setUp(): void
    {
        self::assertInstanceOf(LoggerInterface::class, self::$logger);
        self::assertInstanceOf(Storage::class, self::$storage);
        self::assertInstanceOf(Application::class, self::$app);
    }

    // protected function tearDown(): void
    // {
    // }

    #[DataProvider('providerApp')]
    public function testApp(string $title, array $inData, array $expectedOutData): void
    {
        (self::$logger) && self::$logger->debug('Starting test ' . $title . '\n\n');
        $request = new Request('', '', '', 0, '', '', [], [], []);
        $request->__unserialize($inData);

        /** @psalm-suppress PossiblyNullReference */
        $response = self::$app->processRequest($request)->__serialize();
        $response['id'] = 'test-response';
        $response['time'] = 0;

        (self::$logger) && self::$logger->debug('Done test ' . $title . '\n\n');
        self::assertEquals($expectedOutData, $response);
    }

    /** @return list<TestJsonItem> */
    private static function fetchTestJson(): array
    {
        $path = __DIR__ . '/../../common/test.json';
        if (!file_exists($path)) {
            self::fail('Test JSON file does not exist');
            return [];
        }

        $contents = file_get_contents($path);
        if (!is_string($contents)) {
            self::fail('Test JSON file cannot be read');
            return [];
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            self::fail('Test JSON file cannot be decoded');
            return [];
        }

        $ret = [];
        foreach ($decoded as $item) {
            if (
                !is_array($item) || count($item) !== 3 ||
                !isset($item['title']) || !is_string($item['title']) ||
                !isset($item['act']) || !is_array($item['act']) ||
                !isset($item['exp']) || !is_array($item['exp']) ||
                !isset($item['act']['method']) || !is_string($item['act']['method']) ||
                !isset($item['act']['url']) || !is_string($item['act']['url']) ||
                (isset($item['act']['data']) && !is_array($item['act']['data'])) ||
                !isset($item['exp']['code']) || !is_int($item['exp']['code']) ||
                (isset($item['exp']['data']) && !is_array($item['exp']['data']))
            ) {
                self::fail('Invalid item ' . ((string) json_encode($item)));
                continue;
            }
            $actKeys = array_keys($item['act']);
            sort($actKeys);
            $actKeysStr = implode(',', $actKeys);
            if ($actKeysStr !== 'method,url' && $actKeysStr !== 'data,method,url') {
                self::fail('Invalid* item ' . ((string) json_encode($item)));
                continue;
            }
            $expKeys = array_keys($item['exp']);
            sort($expKeys);
            $expKeysStr = implode(',', $expKeys);
            if ($expKeysStr !== 'code' && $expKeysStr !== 'code,data') {
                self::fail('Invalid** exp "' . $expKeysStr . '" ' . ((string) json_encode($item)));
                continue;
            }
            /** @var TestJsonItem */
            $ret[] = $item;
        }
        return $ret;
    }

    public static function providerApp(): array
    {
        $req = function (string $method, string $url, array $data = []): array {
            return [
                    'id' => 'test-request',
                    'type' => 'request',
                    'sender' => '0.1.2.3',
                    'recipient' => '4.5.6.7',
                    'time' => 0,
                    'object' => $url,
                    'verb' => $method,
                    'data' => $data,
                    'search' => [],
                    'sort' => [],
            ];
        };
        $rsp = function (string $method, string $url, int $code, array $data = []): array {
            return [
                    'id' => 'test-response',
                    'type' => 'response',
                    'sender' => '4.5.6.7',
                    'recipient' => '0.1.2.3',
                    'time' => 0,
                    'object' => $url,
                    'verb' => $method,
                    'data' => $data,
                    'code' => $code,
                    'requestId' => 'test-request',
            ];
        };

        $ret = [];
        $json = self::fetchTestJson();
        foreach ($json as $item) {
            $ret[$item['title']] = [
                $item['title'],
                $req($item['act']['method'], $item['act']['url'], $item['act']['data'] ?? []),
                $rsp($item['act']['method'], $item['act']['url'], $item['exp']['code'], $item['exp']['data'] ?? [])
            ];
        }
        return $ret;
    }
}
