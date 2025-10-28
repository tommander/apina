<?php

declare(strict_types=1);

namespace Apina\Helpers;

use Psr\Log\LoggerInterface;

final class PreviewHelper
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

            $scaled = imagescale($original, 256);
            if ($scaled === false) {
                return false;
            }

            ob_start();
            try {
                imagewebp($scaled);
                $imagestr = ob_get_contents();
            } finally {
                ob_end_clean();
            }

            if ($imagestr === false || empty($imagestr)) {
                return false;
            }

            $b64 = base64_encode($imagestr);
            return 'data:image/webp;base64,' . $b64;
        } catch (\Throwable $e) {
            $logger->warning($e->__toString());
            return false;
        }
    }
}
