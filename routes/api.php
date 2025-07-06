<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', App\Http\Controllers\Api\Categories\IndexController::class)
    ->middleware([
        'cacheResponse:3600',
        'auth:sanctum',
    ])
    ->name('api.categories.index');

Route::get('/products', App\Http\Controllers\Api\Products\IndexController::class)
    ->middleware([
                'cacheResponse:3600',
                'auth:sanctum',
    ])
    ->name('api.products.index');
