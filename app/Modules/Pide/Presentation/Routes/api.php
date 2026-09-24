<?php

declare(strict_types=1);

use App\Modules\Pide\Presentation\Controllers\PideController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('api/lf-itse')->group(function () {
    // ── PIDE: RENIEC ─────────────────────────────────────────────────────────
    Route::get('reniec/consultar/', [PideController::class, 'reniec'])
        ->name('pide.reniec.consultar');

    // ── PIDE: SUNAT ──────────────────────────────────────────────────────────
    Route::get('sunat/consultar/', [PideController::class, 'sunat'])
        ->name('pide.sunat.consultar');
});
