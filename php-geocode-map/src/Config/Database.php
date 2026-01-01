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
            $dbPath = $_ENV['DB_PATH'] ?? 'storage/database/geocode.sqlite';
            $fullPath = PathHelper::basePath($dbPath);

            PathHelper::ensureDirectoryExists($fullPath);

            self::$instance = new Medoo([
                'type' => 'sqlite',
                'database' => $fullPath,
                'option' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ],
                'command' => [
                    'PRAGMA foreign_keys = ON',
                    'PRAGMA encoding = "UTF-8"'
                ]
            ]);
        }

        return self::$instance;
    }

    public static function initSchema(): void
    {
        $db = self::getConnection();

        $db->query("
            CREATE TABLE IF NOT EXISTS locations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                address_id INTEGER UNIQUE NOT NULL,
                title TEXT NOT NULL,
                address TEXT NOT NULL,
                latitude REAL,
                longitude REAL,
                status TEXT DEFAULT 'pending',
                error_message TEXT,
                geocoded_at TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT
            )
        ");

        $db->query("CREATE INDEX IF NOT EXISTS idx_locations_status ON locations(status)");
        $db->query("CREATE INDEX IF NOT EXISTS idx_locations_address_id ON locations(address_id)");
    }
}
