<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Config\Logger;
use App\Controllers\FileController;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use Dotenv\Dotenv;

if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {
    Logger::exception($e);

    $isDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

    Response::error(
        'INTERNAL_ERROR',
        $isDebug ? $e->getMessage() : 'An internal error occurred',
        500
    );
});

try {
    \App\Config\Database::initSchema();
} catch (Exception $e) {
    Logger::error('Database initialization failed: ' . $e->getMessage());
}

$fileController = new FileController();

$router = new Router();

$router
    ->post('/api/files', handler: fn(Request $r, array $p) => $fileController->upload($r))
    ->get('/api/files/{fileId}/download', fn(Request $r, array $p) => $fileController->download($r, $p))
    ->get('/api/files/{fileId}', fn(Request $r, array $p) => $fileController->getMetadata($r, $p))
    ->get('/api/files', fn(Request $r, array $p) => $fileController->list($r))
    ->delete('/api/files/{fileId}', fn(Request $r, array $p) => $fileController->delete($r, $p));

$request = new Request();
$router->dispatch($request);
