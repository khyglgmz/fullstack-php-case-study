<?php

declare(strict_types=1);

namespace App\Config;

use App\Helpers\PathHelper;
use Medoo\Medoo;
use PDO;

class Database
{
    private static ?Medoo $instance = null;

    public static function getConnection(): Medoo
    {
        if (self::$instance === null) {
            $dbPath = $_ENV['DB_PATH'] ?? 'storage/database/file_transfer.sqlite';
            $fullPath = PathHelper::basePath($dbPath);

            PathHelper::ensureDirectoryExists($fullPath);

            self::$instance = new Medoo([
                'type' => 'sqlite',
                'database' => $fullPath,
                'option' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ],
                'command' => ['PRAGMA foreign_keys = ON']
            ]);
        }

        return self::$instance;
    }

    public static function initSchema(): void
    {
        $db = self::getConnection();

        $db->query("
            CREATE TABLE IF NOT EXISTS files (
                id TEXT PRIMARY KEY,
                original_name TEXT NOT NULL,
                stored_name TEXT NOT NULL,
                description TEXT,
                size INTEGER NOT NULL,
                mime_type TEXT NOT NULL,
                checksum TEXT NOT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT,
                deleted_at TEXT
            )
        ");

        $db->query("CREATE INDEX IF NOT EXISTS idx_files_deleted_at ON files(deleted_at)");
        $db->query("CREATE INDEX IF NOT EXISTS idx_files_created_at ON files(created_at)");
    }
}
