<?php

use App\Http\Controllers\Api\V1\UrlController;
use App\Http\Controllers\RedirectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/shorten', [UrlController::class, 'store']);
    Route::get('/urls', [UrlController::class, 'index']);
    Route::delete('/urls/{id}', [UrlController::class, 'destroy']);
});

Route::get('/urls/{short_code}', [RedirectController::class, 'redirect']);
