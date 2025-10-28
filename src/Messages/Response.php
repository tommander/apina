<?php

declare(strict_types=1);

namespace Apina\Messages;

/**
 * @property-read string $requestId
 */
final class Response extends Message
{
    /** @param int<100,599> $code */
    public function __construct(
        string $id,
        string $sender,
        string $recipient,
        int $time,
        string $object,
        string $verb,
        array $data,
        int $code,
        protected string $requestId,
    ) {
        parent::__construct('response', $id, $sender, $recipient, $time, $object, $verb, $data, $code);
        $this->publiclyReadable[] = 'requestId';
    }
}
