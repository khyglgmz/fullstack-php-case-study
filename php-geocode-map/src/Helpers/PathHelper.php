<?php

declare(strict_types=1);

namespace App\Helpers;

class PathHelper
{
    public static function basePath(string $relativePath = ''): string
    {
        $basePath = dirname(__DIR__, 2);

        if ($relativePath === '') {
            return $basePath;
        }

        return $basePath . '/' . ltrim($relativePath, '/');
    }

    public static function ensureDirectoryExists(string $path): void
    {
        $dir = is_dir($path) ? $path : dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
