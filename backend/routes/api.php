<?php

declare(strict_types=1);

use App\Presentation\Http\Controllers\DashboardController;
use App\Presentation\Http\Controllers\OperationsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/user', fn () => response()->json(['id' => Auth::id()]));
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
    Route::get('/operations', [OperationsController::class, 'index']);
});
