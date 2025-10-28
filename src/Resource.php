<?php

declare(strict_types=1);

namespace Apina;

use Apina\Enums\ValueType;
use Apina\Helpers\Helper;
use Apina\Storages\Storage;
use Psr\Log\LoggerAwareTrait;

/**
 * @psalm-type ResourceAttrDefinition = array{source: string, type: string, required?: bool, key?: string}
 * @psalm-type ResourceAttrDefinitions = array<non-empty-string, ResourceAttrDefinition>
 *
 * @property string $id
 * @property string $type
 * @property-read string|false $lastError
 * @property bool $fullDataForUnserialize
 */
final class Resource
{
    use LoggerAwareTrait;

    public function __construct(
        private Storage $storage,
        private string $id,
        private string $type, // resource, gallery, photo, section, ...
        private string|false $lastError = false,
        private bool $fullDataForUnserialize = false,
    ) {
    }

    public static function newFromHref(Storage $storage, string $href): static
    {
        $parts = self::explodeHref($href);
        return new static($storage, $parts['id'], $parts['type']);
    }

    /**
     * @return array{type: string, id: string}
     */
    public static function explodeHref(string $href): array
    {
        if (preg_match('~^/(?<type>[^/\n\r\0]+)/?(?<id>[^/\n\r\0]+)?$~', $href, $matches) !== 1) {
            return ['type' => '', 'id' => ''];
        }
        return [
            'type' => $matches['type'] ?? '',
            'id' => $matches['id'] ?? '',
        ];
    }

    public static function idFromHref(string $href): string
    {
        return self::explodeHref($href)['id'];
    }

    public static function typeFromHref(string $href): string
    {
        return self::explodeHref($href)['type'];
    }

    /**
     * Registers a new resource or overwrites an existing one.
     *
     * @param non-empty-string $type
     * @param array<non-empty-string, array{source: string, required: boolean, type: ValueType}> $attr
     */
    public function registerResource(string $type, array $attr): void
    {
        if ($type === 'resource') {
            return;
        }
        $this->storage->setObject("/resource/$type", ['attr' => $attr]);
    }

    public function unregisterResource(string $type): void
    {
        if ($type === 'resource') {
            return;
        }
        $this->storage->deleteObject("/resource/$type");
    }

    /**
     * @return non-empty-string
     */
    public function href(): string
    {
        return "/" . $this->type . "/" . $this->id;
    }

    public function valid(): bool
    {
        return $this->storage->hasObject($this->href());
    }

    public function removeFromStorage(): bool
    {
        $this->storage->deleteObject($this->href());
        return (!$this->valid());
    }

    /**
     * @return array{type: string, name: string}
     */
    public static function explodeAttr(string $attr): array
    {
        if (preg_match('/^(?<type>[^:]+):(?<name>.+)$/', $attr, $matches) !== 1) {
            return ['type' => '', 'name' => ''];
        }
        return [
            'type' => $matches['type'] ?? '',
            'name' => $matches['name'] ?? '',
        ];
    }

    public static function nameFromAttr(string $attr): string
    {
        return self::explodeAttr($attr)['name'];
    }

    public static function typeFromAttr(string $attr): string
    {
        return self::explodeAttr($attr)['type'];
    }

    /**
     * Returns the stored value from the given attribute source, e.g. "meta:name".
     */
    protected function getAttr(string $attr): mixed
    {
        $parts = self::explodeAttr($attr);
        if ($parts['type'] === 'meta') {
            if (isset($parts['name']) && !empty($parts['name'])) {
                return $this->storage->getObjectMeta($this->href(), $parts['name']);
            }
        }
        if ($parts['type'] === 'file') {
            return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ltrim($parts['name'], DIRECTORY_SEPARATOR));
        }
        return null;
    }

    /**
     * Stores the given value, any, in the given attribute source.
     *
     * ```php
     * setAttr('meta:name', 'John Doe');
     * setAttr('meta:age', 123);
     * setAttr('meta:alive', true);
     * setAttr('meta:hobbies', ['Staying alive', 'Sitting', 'Interviews']);
     * setAttr('meta:articles', ['/article/news-2012-12-19', '/article/news-2012-12-20', '/article/news-2012-12-21']);
     * setAttr('add:articles', '/article/news-2012-12-22');
     * setAttr('rem:articles', '/article/news-2012-12-21');
     * setAttr('file:somejson', '../data/data.json');
     * ```
     */
    protected function setAttr(string $attr, mixed $value): void
    {
        $parts = self::explodeAttr($attr);

        if ($parts['type'] === 'meta') {
            ($this->logger) && $this->logger->debug('META! ' . $attr . ' ?_?_? ' . (string) json_encode($value));
            if (isset($parts['name']) && !empty($parts['name'])) {
                $this->storage->setObjectMeta($this->href(), $parts['name'], $value);
            }
            return;
        }
        if ($parts['type'] === 'file') {
            if (is_string($value) || is_resource($value)) {
                file_put_contents($parts['name'], $value);
            }
            return;
        }
        if ($parts['type'] === 'add') {
            if (!isset($parts['name']) || empty($parts['name'])) {
                ($this->logger) && $this->logger->debug('Error: add: has empty name');
                return;
            }
            /** @var mixed */
            $meta = $this->storage->getObjectMeta($this->href(), $parts['name']);
            if (!is_array($meta)) {
                $meta = [];
            }
            /** @psalm-suppress MixedAssignment */
            $meta[] = $value;
            $this->storage->setObjectMeta($this->href(), $parts['name'], $meta);
            return;
        }

        if ($parts['type'] === 'rem') {
            if (!isset($parts['name']) || empty($parts['name'])) {
                ($this->logger) && $this->logger->debug('Error: add: has empty name');
                return;
            }
            /** @var mixed */
            $meta = $this->storage->getObjectMeta($this->href(), $parts['name']);
            if (!is_array($meta)) {
                $meta = [];
            }
            $metaNew = array_values(array_filter($meta, fn ($item) => ($item !== $value)));
            $this->storage->setObjectMeta($this->href(), $parts['name'], $metaNew);
            return;
        }
    }

    protected function getTypeAttrs(): array
    {
        $attr = $this->storage->getObject('/resource/' . $this->type);
        if ($attr === null) {
            return [];
        }
        return $attr;
    }

    /**
     * Allows accessing "id" and custom attributes as if they were public props of this class.
     */
    public function __get(string $name): mixed
    {
        if (in_array($name, ['id', 'type', 'lastError', 'fullDataForUnserialize'], true)) {
            return $this->$name;
        }
        $attr = $this->getTypeAttrs();
        if (isset($attr[$name]) && isset($attr[$name]['source']) && is_string($attr[$name]['source'])) {
            return $this->getAttr($attr[$name]['source']);
        }
        return null;
    }

    public function __set(string $name, mixed $value): void
    {
        if (in_array($name, ['id', 'type', 'lastError', 'fullDataForUnserialize'], true)) {
            if (is_bool($value) && $name === 'fullDataForUnserialize') {
                $this->$name = $value;
            }
            if (is_string($value) && $name !== 'lastError') {
                $this->$name = $value;
            }
            return;
        }
        $attr = $this->getTypeAttrs();
        if (isset($attr[$name]) && isset($attr[$name]['source']) && is_string($attr[$name]['source'])) {
            $this->setAttr($attr[$name]['source'], $value);
            return;
        }
    }

    private function determineValueType(mixed $something): ValueType
    {
        if (is_string($something)) {
            return ValueType::V_STRING;
        }
        if (is_int($something)) {
            return ValueType::V_INT;
        }
        if (is_float($something)) {
            return ValueType::V_FLOAT;
        }
        if (is_bool($something)) {
            return ValueType::V_BOOL;
        }
        if (is_array($something)) {
            return ValueType::V_ARRAY;
        }
        return ValueType::V_NULL;
    }

    public function __serialize(): array
    {
        return $this->storage->getObject($this->href()) ?? [];
    }


    /**
     * @psalm-assert-if-true ResourceAttrDefinitions $data
     */
    public function validateResourceDefinition(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (!is_string($key) || empty($key)) {
                return false;
            }
            if (!is_array($value)) {
                return false;
            }
            if (!Helper::arrayAllowedKeys($value, ['source', 'type', 'key', 'required'])) {
                return false;
            }
            if (
                !isset($value['source']) || !is_string($value['source']) ||
                !isset($value['type']) || !is_string($value['type'])
            ) {
                return false;
            }
            if (
                (isset($value['key']) && !is_bool($value['key'])) ||
                (isset($value['required']) && !is_bool($value['required']))
            ) {
                return false;
            }
        }
        return true;
    }

    public function __unserialize(array $data): void
    {
        /** Flag indicating whether we found at least one known attribute with correct value. */
        $attrFlag = false;
        $this->lastError = false;
        if (count($data) === 0) {
            $this->lastError = 'Invalid object data; empty.';
            return;
        }

        if ($this->type === 'resource') {
            ($this->logger) && $this->logger->debug('Resource detected');
            if (!$this->validateResourceDefinition($data)) {
                $this->lastError = 'Invalid resource definition data.';
                return;
            }
            foreach ($data as $attrName => $attrSource) {
                ($this->logger) && $this->logger->debug('Checking attr ' . $attrName);
                $this->setAttr('meta:' . $attrName, $attrSource);
            }
            ($this->logger) && $this->logger->debug('Resource done');
            return;
        }

        $attr = $this->getTypeAttrs();
        if (count($attr) === 0) {
            $this->lastError = 'Invalid object type; no attributes.';
            return;
        }

        /** @var mixed $attrSource */
        foreach ($attr as $attrName => $attrSource) {
            if (!is_string($attrName) || !is_array($attrSource)) {
                continue;
            }
            if (!isset($data[$attrName])) {
                // If a resource attribute is marked as required, it must be present when
                // the object is invalid, or
                // the presence of required attribute is enforced.
                if (isset($attrSource['required']) && $attrSource['required'] === true && ((!$this->valid()) || $this->fullDataForUnserialize)) {
                    $this->lastError = 'Invalid object data; missing required field "' . $attrName . '".';
                    return;
                }
                continue;
            }
            $valueType = $this->determineValueType($data[$attrName]);
            if ($valueType->value !== $attrSource['type']) {
                $this->lastError = 'Invalid object data; invalid value type.';
                return;
            }
            $attrFlag = true;
            if ($this->id === '' && isset($attrSource['key']) && $attrSource['key'] === true && is_string($data[$attrName])) {
                $this->id = $data[$attrName];
                $prefix = $this->id;
                $counter = 2;
                while ($this->valid()) {
                    $this->id = $prefix . strval($counter++);
                }
            }
        }
        if (!$attrFlag) {
            $this->lastError = 'Invalid object data; no known attribute included.';
            return;
        }

        /** @var mixed $attrSource */
        foreach ($attr as $attrName => $attrSource) {
            if (!is_array($attrSource) || !isset($attrSource['source']) || !is_string($attrSource['source'])) {
                continue;
            }
            isset($data[$attrName]) && $this->setAttr($attrSource['source'], $data[$attrName]);
        }
    }

    public function serialize(): string
    {
        $dataRaw = $this->__serialize();
        $json = json_encode($dataRaw);
        if (!is_string($json) || json_last_error() !== JSON_ERROR_NONE) {
            return '{}';
        }
        return $json;
    }

    public function unserialize(string $data): void
    {
        $decoded = json_decode($data, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return;
        }
        $this->__unserialize($decoded);
    }
}
