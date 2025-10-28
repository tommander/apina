<?php

declare(strict_types=1);

namespace Apina\Helpers;

final class Helper
{
    public static function anyToStr(mixed $something, string $default = ''): string
    {
        if (is_string($something)) {
            return $something;
        }

        if (is_null($something) || (is_object($something) && method_exists($something, '__toString')) || is_scalar($something)) {
            return strval($something);
        }

        return $default;
    }

    /**
     * @param non-empty-string $default
     * @return non-empty-string
     */
    public static function anyToNonEmptyStr(mixed $something, string $default = ' '): string
    {
        $str = self::anyToStr($something, $default);
        if (empty($str)) {
            return $default;
        }
        return $str;
    }

    /**
     * @param array{negative?: bool, zero?: bool, positive?: bool} $options
     */
    public static function anyToInt(mixed $something, int $default = 0, array $options = []): int
    {
        $int = is_int($something) ? $something : intval($something);
        if (
            (!($options['negative'] ?? true) && $int < 0) ||
            (!($options['zero'] ?? true) && $int === 0) ||
            (!($options['positive'] ?? true) && $int > 0)
        ) {
            return $default;
        }

        return $int;
    }

    /**
     * @param non-positive-int $default
     * @return non-positive-int
     */
    public static function anyToNonPositiveInt(mixed $something, int $default = 0): int
    {
        $int = self::anyToInt($something, $default);
        if ($int > 0) {
            return $default;
        }
        return $int;
    }

    /**
     * @param non-negative-int $default
     * @return non-negative-int
     */
    public static function anyToNonNegativeInt(mixed $something, int $default = 0): int
    {
        $int = self::anyToInt($something, $default);
        if ($int < 0) {
            return $default;
        }
        return $int;
    }

    /**
     * @param negative-int $default
     * @return negative-int
     */
    public static function anyToNegativeInt(mixed $something, int $default = -1): int
    {
        $int = self::anyToInt($something, $default);
        if ($int >= 0) {
            return $default;
        }
        return $int;
    }

    /**
     * @param positive-int $default
     * @return positive-int
     */
    public static function anyToPositiveInt(mixed $something, int $default = 1): int
    {
        $int = self::anyToInt($something, $default);
        if ($int <= 0) {
            return $default;
        }
        return $int;
    }

    /**
     * @param list<string> $default
     * @return list<string>
     */
    public static function anyToStringList(mixed $something, array $default = []): array
    {
        if (!is_array($something)) {
            return $default;
        }
        $ret = [];
        foreach ($something as $some) {
            if (!is_string($some)) {
                continue;
            }
            $ret[] = $some;
        }
        return $ret;
    }

    /**
     * @param list<non-empty-string> $default
     * @return list<non-empty-string>
     */
    public static function anyToNonEmptyStringList(mixed $something, array $default = []): array
    {
        if (!is_array($something)) {
            return $default;
        }
        $ret = [];
        foreach ($something as $some) {
            if (!is_string($some) || empty($some)) {
                continue;
            }
            $ret[] = $some;
        }
        return $ret;
    }

    public static function untrim(string $text, string $startend): string
    {
        return self::luntrim(self::runtrim($text, $startend), $startend);
    }

    public static function luntrim(string $text, string $start): string
    {
        return ((!str_starts_with($text, $start)) ? $start : '') . $text;
    }

    public static function runtrim(string $text, string $end): string
    {
        return $text . ((!str_ends_with($text, $end)) ? $end : '');
    }

    public static function sameArrays(array $arr1, array $arr2): bool
    {
        return (
            (count($arr1) === count($arr2)) &&
            (count(array_diff($arr1, $arr2)) === 0) &&
            (count(array_diff($arr2, $arr1)) === 0)
        );
    }

    /**
     * @param array<\Serializable> $arr1
     * @param array<\Serializable> $arr2
     */
    public static function sameObjectLists(array $arr1, array $arr2): bool
    {
        $sarr1 = [];
        foreach ($arr1 as $item1) {
            $sitem1 = $item1->serialize();
            if ($sitem1 === null) {
                continue;
            }
            $sarr1[] = $sitem1;
        }
        $sarr2 = [];
        foreach ($arr2 as $item2) {
            $sitem2 = $item2->serialize();
            if ($sitem2 === null) {
                continue;
            }
            $sarr2[] = $sitem2;
        }
        return (
            (count($sarr1) === count($sarr2)) &&
            (count(array_diff($sarr1, $sarr2)) === 0) &&
            (count(array_diff($sarr2, $sarr1)) === 0)
        );
    }

    /**
     * Verifies that the given array has only the allowed keys (not all must be set)
     */
    public static function arrayAllowedKeys(array $arr, array $allowedKeys): bool
    {
        $karr = array_filter($arr, fn ($key) => !in_array($key, $allowedKeys, true), ARRAY_FILTER_USE_KEY);
        return (count($karr) === 0);
    }

    /**
     * @return array{object: string, id: string|null, query: array|null}
     */
    public static function explodeUrl(string $pathBase = '', string|null $path = null): array
    {
        $rawPath = $path ?? $_SERVER['REQUEST_URI'] ?? '/';
        if ($pathBase !== '') {
            $rawPath = str_replace($pathBase, '', $rawPath);
        }
        $temp1 = explode('/', $rawPath);
        $return = [
            'object' => '',
            'id' => null,
            'query' => null,
        ];
        if (count($temp1) === 1) {
            $temp2 = explode('?', $temp1[0]);
            $return['object'] = $temp2[0];
        } else {
            $return['object'] = $temp1[0];
            $temp2 = explode('?', $temp1[1]);
            $return['id'] = $temp2[0];
        }
        if (count($temp2) > 1) {
            parse_str($temp2[1], $return['query']);
        }

        return $return;
    }
}
