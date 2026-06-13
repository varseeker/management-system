<?php

use App\Http\Controllers\Api\MenuApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Middleware\VerifyInventoryApiToken;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyInventoryApiToken::class)->group(function () {
    Route::get('/menus', [MenuApiController::class, 'index']);
    Route::post('/orders', [OrderApiController::class, 'store']);
});
