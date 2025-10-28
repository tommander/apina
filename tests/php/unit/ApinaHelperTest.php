<?php

declare(strict_types=1);

namespace Apina\Tests;

use Apina\Loggers\MemoryLogger;
use Apina\Loggers\FileLogger;
use Apina\Messages\Request;
use Apina\Application;
use Apina\Helpers\ApinaHelper;
use Apina\Storages\JsonStorage;
use Apina\Storages\MemoryStorage;
use Apina\Storages\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @psalm-type TestJsonItem = array{title: string, act: array{method: string, url: string, data?: array}, exp: array{method: string, url: string, code: int, data?: array}}
 */
final class ApinaHelperTest extends TestCase
{
    private static LoggerInterface|null $logger = null;
    private static Storage|null $storage = null;
    private static ApinaHelper|null $apihlp = null;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$logger = new MemoryLogger();
        // self::$logger = new FileLogger(__DIR__ . '/../../../log_phpunit.txt');

        self::$storage = new MemoryStorage();
        // self::$storage = new JsonStorage(__DIR__ . '/../../../_storage_phpunit.json');
        self::$storage->setLogger(self::$logger);

        self::$apihlp = new ApinaHelper(self::$storage, self::$logger);
    }

    #[\Override]
    protected function setUp(): void
    {
        self::assertInstanceOf(LoggerInterface::class, self::$logger);
        self::assertInstanceOf(Storage::class, self::$storage);
        self::assertInstanceOf(ApinaHelper::class, self::$apihlp);
    }

    /**
     * @param array{method: string, params: array} $inData
     */
    #[DataProvider('providerHelper')]
    public function testHelper(string $title, array $inData, array $expectedOutData): void
    {
        (self::$logger) && self::$logger->debug('Starting test ' . $title . '\n\n');
        self::assertArrayHasKey('method', $inData);

        $response = call_user_func([self::$apihlp, $inData['method']], ...$inData['params']);
        if (!is_array($response) || !isset($response['code']) || !isset($response['data']) || !is_int($response['code'])) {
            self::fail();
        }

        (self::$logger) && self::$logger->debug('Done test ' . $title . '\n\n');
        self::assertEquals($expectedOutData, $response);
    }

    public static function providerHelper(): array
    {
        $ret = [];
        $json = self::fetchTestJson();
        foreach ($json as $item) {
            $ret[$item['title']] = [
                $item['title'],
                $item['act'],
                $item['exp']
            ];
        }
        return $ret;
    }

    /** @return list<TestJsonItem> */
    private static function fetchTestJson(): array
    {
        $path = __DIR__ . '/../../common/test2.json';
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
                !isset($item['act']['params']) || !is_array($item['act']['params']) ||
                !isset($item['exp']['code']) || !is_int($item['exp']['code']) ||
                (isset($item['exp']['data']) && !is_array($item['exp']['data']))
            ) {
                self::fail('Invalid item ' . ((string) json_encode($item)));
                continue;
            }
            $actKeys = array_keys($item['act']);
            sort($actKeys);
            $actKeysStr = implode(',', $actKeys);
            if ($actKeysStr !== 'method,params') {
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
}
