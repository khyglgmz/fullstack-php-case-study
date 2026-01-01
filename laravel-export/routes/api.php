<?php

use App\Http\Controllers\Api\ExportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('export')->group(function () {
    Route::get('/products', [ExportController::class, 'products']);
    Route::post('/products/async', [ExportController::class, 'productsAsync']);
});
