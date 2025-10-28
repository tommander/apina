<?php

declare(strict_types=1);

namespace Apina\Messages;

/**
 * @property-read array $search
 * @property-read array $sort
 */
final class Request extends Message
{
    public function __construct(
        string $id,
        string $sender,
        string $recipient,
        int $time,
        string $object,
        string $verb,
        array $data,
        protected array $search,
        protected array $sort,
    ) {
        parent::__construct('request', $id, $sender, $recipient, $time, $object, $verb, $data, 200);
        $this->publiclyReadable[] = 'search';
        $this->publiclyReadable[] = 'sort';
    }
}
