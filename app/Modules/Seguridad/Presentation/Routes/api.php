<?php

declare(strict_types=1);

use App\Modules\Seguridad\Presentation\Controllers\JwtAuthController;
use App\Modules\Seguridad\Presentation\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    // ── Públicas (sin auth) ────────────────────────────────────────────────
    Route::post('auth/jwt/create/', [JwtAuthController::class, 'create'])
        ->middleware('throttle:login')
        ->name('auth.jwt.create');
    Route::post('auth/jwt/refresh/', [JwtAuthController::class, 'refresh'])
        ->name('auth.jwt.refresh');
    Route::post('auth/jwt/verify/', [JwtAuthController::class, 'verify'])
        ->name('auth.jwt.verify');

    // ── Protegidas (requieren JWT) ─────────────────────────────────────────
    Route::middleware('auth:api')->group(function () {
        Route::get('auth/users/me/', [UserController::class, 'me'])
            ->name('auth.users.me');
    });
});
