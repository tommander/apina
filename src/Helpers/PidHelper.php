<?php

declare(strict_types=1);

namespace Apina\Helpers;

use Psr\Log\LoggerInterface;

/**
 * @psalm-type PidData = list<list{int, int, int}>
 */
final class PidHelper
{
    public static function generate(string $path, LoggerInterface $logger): string|false
    {
        try {
            if (!file_exists($path)) {
                return false;
            }

            $raw = file_get_contents($path);
            if (!is_string($raw)) {
                return false;
            }

            $original = imagecreatefromstring($raw);
            if ($original === false) {
                return false;
            }

            $scaled = imagescale($original, 100, 100);
            if ($scaled === false) {
                return false;
            }

            $pid = [];
            $areas = [[10, 10], [25, 90], [48, 74], [68, 23], [92, 45]];
            $areaAvg = function (int $cx, int $cy, int $size) use ($scaled): array {
                $r = 0;
                $g = 0;
                $b = 0;
                $i = 0;
                for ($x = $cx - $size; $x <= $cx + $size; $x++) {
                    for ($y = $cy - $size; $y <= $cy + $size; $y++) {
                        $color = imagecolorat($scaled, $x, $y);
                        if ($color === false) {
                            continue;
                        }
                        /** @var array{red: int, green: int, blue: int, alpha: int} $colorArr */
                        $colorArr = imagecolorsforindex($scaled, $color);
                        $r += $colorArr['red'];
                        $g += $colorArr['green'];
                        $b += $colorArr['blue'];
                        $i++;
                    }
                }
                if ($i === 0) {
                    return [0, 0, 0];
                }

                return [intval($r / $i), intval($g / $i), intval($b / $i)];
            };

            foreach ($areas as $area) {
                $pid[] = $areaAvg($area[0], $area[1], 3);
            }

            $pidJson = json_encode($pid);
            if ($pidJson === false) {
                return false;
            }

            return base64_encode($pidJson);
        } catch (\Throwable $e) {
            $logger->warning($e->__toString());
            return false;
        }
    }

    /**
     * @return PidData|false
     */
    public static function decode(string $pid): array|false
    {
        $decoded = base64_decode($pid, true);
        if ($decoded === false) {
            return false;
        }

        $arr = json_decode($decoded, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($arr) || count($arr) !== 5) {
            return false;
        }

        foreach ($arr as $avg) {
            if (!is_array($avg) || count($avg) !== 3 || !is_int($avg[0]) || !is_int($avg[1]) || !is_int($avg[2])) {
                return false;
            }
        }

        /**
         * @var PidData $arr
         */
        return $arr;
    }

    public static function compare(string $pid1, string $pid2): int|false
    {
        $data1 = static::decode($pid1);
        if ($data1 === false) {
            return false;
        }
        $data2 = static::decode($pid2);
        if ($data2 === false) {
            return false;
        }
        return (
            abs($data1[0][0] - $data2[0][0]) +
            abs($data1[0][1] - $data2[0][1]) +
            abs($data1[0][2] - $data2[0][2]) +
            abs($data1[1][0] - $data2[1][0]) +
            abs($data1[1][1] - $data2[1][1]) +
            abs($data1[1][2] - $data2[1][2]) +
            abs($data1[2][0] - $data2[2][0]) +
            abs($data1[2][1] - $data2[2][1]) +
            abs($data1[2][2] - $data2[2][2]) +
            abs($data1[3][0] - $data2[3][0]) +
            abs($data1[3][1] - $data2[3][1]) +
            abs($data1[3][2] - $data2[3][2]) +
            abs($data1[4][0] - $data2[4][0]) +
            abs($data1[4][1] - $data2[4][1]) +
            abs($data1[4][2] - $data2[4][2])
        );
    }

    public static function same(string $pid1, string $pid2): bool
    {
        $cmp = static::compare($pid1, $pid2);

        return ($cmp !== false) && ($cmp < 100);
    }
}
