<?php

use App\Http\Controllers\Api\ExportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/export/products', [ExportController::class, 'showForm']);

Route::get('/export/download/{filename}', function (string $filename) {
    $path = 'exports/' . basename($filename);

    if (!Storage::disk('local')->exists($path)) {
        abort(404, 'File not found');
    }

    return Storage::disk('local')->download($path);
})->where('filename', '.*\.json');
