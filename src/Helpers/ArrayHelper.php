<?php

declare(strict_types=1);

namespace Apina\Helpers;

final class ArrayHelper
{
    public static function readValue(mixed &$data, string|int ...$path): mixed
    {
        $tmp = $data;
        if (count($path) > 0 && !is_array($data)) {
            return null;
        }
        foreach ($path as $pathItem) {
            if (!isset($tmp[$pathItem])) {
                return null;
            }
            /** @psalm-suppress MixedAssignment,MixedArrayAccess */
            $tmp = $tmp[$pathItem];
            if (!is_array($tmp)) {
                break;
            }
        }
        return $tmp;
    }

    public static function writeValue(mixed &$data, mixed $value, string ...$path): void
    {
        $tmp = &$data;
        if (count($path) > 0 && !is_array($data)) {
            $data = [];
        }
        foreach ($path as $pathItem) {
            if (!isset($tmp[$pathItem])) {
                /** @psalm-suppress MixedArrayAssignment */
                $tmp[$pathItem] = [];
            }
            /** @psalm-suppress MixedArrayAccess */
            $tmp = &$tmp[$pathItem];
        }
        /** @psalm-suppress MixedAssignment */
        $tmp = $value;
    }

    public static function addValue(mixed &$data, mixed $value, string ...$path): void
    {
        $tmp = &$data;
        if (count($path) > 0 && !is_array($data)) {
            $data = [];
        }
        foreach ($path as $pathItem) {
            if (!isset($tmp[$pathItem])) {
                /** @psalm-suppress MixedArrayAssignment */
                $tmp[$pathItem] = [];
            }
            /** @psalm-suppress MixedArrayAccess */
            $tmp = &$tmp[$pathItem];
        }
        if (!is_array($tmp)) {
            $tmp = [];
        }
        /** @psalm-suppress MixedAssignment */
        $tmp[] = $value;
    }
}
