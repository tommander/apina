<?php

declare(strict_types=1);

namespace Apina\Storages;

use Psr\Log\LoggerAwareTrait;

/**
 * @psalm-type ApinaObject = array<non-empty-string, mixed>
 * @psalm-type ApinaDatabase = array<non-empty-string, ApinaObject>
 */
abstract class Storage
{
    use LoggerAwareTrait;

    protected bool $loaded = false;

    public function __construct(
        /** @var ApinaDatabase */
        private array $data = []
    ) {
    }

    abstract protected function load(): bool;
    abstract protected function save(): bool;

    private function maybeLoad(): void
    {
        if ($this->loaded) {
            return;
        }
        $this->loaded = $this->load();
    }

    /**
     * @return ApinaDatabase|null
     */
    public function getDatabase(): array|null
    {
        $this->maybeLoad();
        return $this->data;
    }

    /**
     * @param ApinaDatabase $data
     */
    protected function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @param ApinaDatabase $data
     */
    public function setDatabase(array $data): void
    {
        $this->data = $data;
        $res = $this->save();
        if ($res !== true) {
            throw new \Error('Cannot save storage in setDatabase');
        }
    }

    /**
     * @return list<non-empty-string>
     */
    public function listObjects(): array
    {
        $this->maybeLoad();
        return array_keys($this->data);
    }

    /**
     * @param non-empty-string $objectId
     * @return ApinaObject|null
     */
    public function getObject(string $objectId): array|null
    {
        $this->maybeLoad();
        return $this->data[$objectId] ?? null;
    }

    /**
     * @param non-empty-string $objectId
     */
    public function hasObject(string $objectId): bool
    {
        $this->maybeLoad();
        return isset($this->data[$objectId]);
    }

    /**
     * @param non-empty-string $objectId
     * @param ApinaObject $objectData
     */
    public function setObject(string $objectId, array $objectData): void
    {
        $this->maybeLoad();
        $this->data[$objectId] = $objectData;
        $res = $this->save();
        if ($res !== true) {
            throw new \Error('Cannot save storage in setObject');
        }
    }

    /**
     * @param non-empty-string $objectId
     */
    public function deleteObject(string $objectId): void
    {
        $this->maybeLoad();
        unset($this->data[$objectId]);
        $res = $this->save();
        if ($res !== true) {
            throw new \Error('Cannot save storage in deleteObject');
        }
    }

    /**
     * @param non-empty-string $objectId
     * @param non-empty-string $metaKey
     */
    public function getObjectMeta(string $objectId, string $metaKey): mixed
    {
        $this->maybeLoad();
        if (!isset($this->data[$objectId])) {
            return null;
        }
        return $this->data[$objectId][$metaKey] ?? null;
    }

    /**
     * @param non-empty-string $objectId
     * @param non-empty-string $metaKey
     */
    public function setObjectMeta(string $objectId, string $metaKey, mixed $metaValue): void
    {
        $this->maybeLoad();
        if (!isset($this->data[$objectId])) {
            $this->data[$objectId] = [];
        }
        $this->data[$objectId][$metaKey] = $metaValue;
        $res = $this->save();
        if ($res !== true) {
            throw new \Error('Cannot save storage in setObjectMeta');
        }
    }
}
