<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;
use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

echo "Initializing database...\n";

try {
    Database::initSchema();
    echo "Database initialized successfully!\n";
    echo "Database location: " . ($_ENV['DB_PATH'] ?? 'storage/database/file_transfer.sqlite') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
