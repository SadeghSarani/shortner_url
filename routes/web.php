<?php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/s/{shortCode}', [RedirectController::class, 'redirect']);
Route::get('/{shortCode}', [RedirectController::class, 'redirect']);
