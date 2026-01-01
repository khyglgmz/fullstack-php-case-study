<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => $statusCode >= 200 && $statusCode < 300,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function error(string $code, string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    public static function download(string $filePath, string $fileName, string $mimeType): void
    {
        if (!file_exists($filePath)) {
            self::error('FILE_NOT_FOUND', 'File not found on disk', 404);
        }

        http_response_code(200);
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($filePath);
        exit;
    }

    public static function setCorsHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    public static function handleOptions(): void
    {
        self::setCorsHeaders();
        http_response_code(204);
        exit;
    }
}
