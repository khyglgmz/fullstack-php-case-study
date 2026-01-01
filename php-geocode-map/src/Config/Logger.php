<?php

declare(strict_types=1);

namespace App\Config;

use App\Helpers\PathHelper;

class Logger
{
    private static ?string $logPath = null;

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    private static function log(string $level, string $message, array $context): void
    {
        $logPath = self::getLogPath();

        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';

        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextString}" . PHP_EOL;

        error_log($logEntry, 3, $logPath);
    }

    private static function getLogPath(): string
    {
        if (self::$logPath === null) {
            $path = $_ENV['LOG_PATH'] ?? 'storage/logs/error.log';
            self::$logPath = PathHelper::basePath($path);

            PathHelper::ensureDirectoryExists(self::$logPath);
        }

        return self::$logPath;
    }

    public static function exception(\Throwable $e): void
    {
        self::error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
