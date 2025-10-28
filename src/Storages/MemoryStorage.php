<?php

declare(strict_types=1);

namespace Apina\Storages;

final class MemoryStorage extends Storage
{
    #[\Override]
    protected function load(): bool
    {
        return true;
    }

    #[\Override]
    protected function save(): bool
    {
        return true;
    }
}
