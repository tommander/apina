<?php

declare(strict_types=1);

namespace Apina\Loggers;

use Apina\Helpers\Helper;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

final class FileLogger implements LoggerInterface
{
    use LoggerTrait;

    private mixed $handle = null;
    private EventDispatcherInterface|null $eventDispatcher;

    public function __construct(string $path, EventDispatcherInterface|null $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->handle = fopen($path, 'a');
        $this->debug('Logging started');
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            $this->debug('Logging finished.');
            fclose($this->handle);
            $this->handle = null;
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    #[\Override]
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if (!is_resource($this->handle)) {
            throw new Exception('Logger cannot write to file');
        }

        $logLevels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];
        if (!in_array($level, $logLevels, true)) {
            throw new InvalidArgumentException('');
        }

        $text = strval($message);
        /** @var mixed $value */
        foreach ($context as $key => $value) {
            $text = str_replace('{' . $key . '}', Helper::anyToStr($value), $text);
        }

        $this->eventDispatcher && $this->eventDispatcher->dispatch((object) ['date' => date('c'), 'level' => $level, 'message' => $text]);

        $log = sprintf(
            '[%1$s]{%2$s} %3$s%4$s',
            date('c'),
            $level,
            $text,
            PHP_EOL,
        );

        fwrite($this->handle, $log);
    }
}
