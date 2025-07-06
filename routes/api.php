<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', function (Request $request) {
    $apiVersion = $request->attributes->get('api_version');
    $controllerClass = "App\\Http\\Controllers\\Api\\v{$apiVersion}\\Categories\\IndexController";

    return app()->call($controllerClass);
})
    ->middleware([
        'cacheResponse:3600',
        'auth:sanctum',
        'versionedApi',
    ])
    ->name('api.categories.index');

Route::get('/products', function (Request $request) {
    $apiVersion = $request->attributes->get('api_version');

    $controllerClass = "App\\Http\\Controllers\\Api\\v{$apiVersion}\\Products\\IndexController";

    return app()->call($controllerClass);
})
    ->middleware([
        'cacheResponse:3600',
        'auth:sanctum',
        'versionedApi',
    ])
    ->name('api.products.index');
