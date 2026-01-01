<?php

declare(strict_types=1);

namespace App\Helpers;

class FileHelper
{
    public static function getExtension(string $fileName): string
    {
        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }

    public static function getMimeType(string $filePath): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($filePath);
    }
}
