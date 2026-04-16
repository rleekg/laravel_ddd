<?php

declare(strict_types=1);

use App\Presentation\Http\Controllers\AuthController;
use App\Presentation\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/{any}', [DashboardController::class, 'index'])->where('any', '.*');
});
