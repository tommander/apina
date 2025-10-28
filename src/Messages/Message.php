<?php

declare(strict_types=1);

namespace Apina\Messages;

use Psr\Log\LoggerAwareTrait;

/**
 * @property-read string $type
 * @property-read string $id
 * @property-read string $sender
 * @property-read string $recipient
 * @property-read int $time
 * @property-read string $object
 * @property-read string $verb
 * @property-read array $data
 * @property-read int<100,599> $code
 */
class Message
{
    use LoggerAwareTrait;

    /** @var list<non-empty-string> */
    public array $publiclyReadable;

    public function __construct(
        private string $type,
        private string $id,
        private string $sender,
        private string $recipient,
        private int $time,
        private string $object,
        private string $verb,
        private array $data,
        /** @var int<100,599> */
        private int $code,
    ) {
        $this->publiclyReadable = ['type', 'id', 'sender', 'recipient', 'time', 'object', 'verb', 'data', 'code'];
    }

    public function __get(string $name): mixed
    {
        if ($name === 'data' && $this->verb === 'HEAD') {
            return null;
        }
        if (!in_array($name, $this->publiclyReadable, true)) {
            return null;
        }
        return $this->$name;
    }

    public function __serialize(): array
    {
        $ret = [];
        foreach ($this->publiclyReadable as $param) {
            /** @psalm-suppress MixedAssignment */
            $value = $this->$param;
            if ($param === 'data' && $this->verb === 'HEAD') {
                $ret[$param] = [];
                continue;
            }
            /** @psalm-suppress MixedAssignment */
            $ret[$param] = $value;
        }
        return $ret;
    }

    public function serialize(): string
    {
        $data = $this->__serialize();
        return (string) json_encode($data);
    }

    public function __unserialize(array $data): void
    {
        foreach ($this->publiclyReadable as $param) {
            if (!isset($data[$param])) {
                continue;
            }
            $this->$param = $data[$param];
        }
    }

    public function unserialize(string $data): void
    {
        $decoded = json_decode($data, true);
        if (!is_array($decoded)) {
            return;
        }
        $this->__unserialize($decoded);
    }
}
