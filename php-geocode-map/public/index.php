<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Config\Database;
use App\Config\Logger;
use App\Controllers\GeocodeController;
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
    Database::initSchema();
} catch (Exception $e) {
    Logger::error('Database initialization failed: ' . $e->getMessage());
}

$geocodeController = new GeocodeController();

$router = new Router();

$router
    ->post('/api/geocode', fn(Request $r, array $p) => $geocodeController->geocodeAll($r))
    ->post('/api/geocode/{addressId}', fn(Request $r, array $p) => $geocodeController->retry($r, $p))
    ->get('/api/locations', fn(Request $r, array $p) => $geocodeController->list($r))
    ->post('/api/initialize', fn(Request $r, array $p) => $geocodeController->initialize($r));

$request = new Request();
$router->dispatch($request);
