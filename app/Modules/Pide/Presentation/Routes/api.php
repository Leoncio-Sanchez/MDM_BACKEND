<?php

declare(strict_types=1);

use App\Modules\Pide\Presentation\Controllers\PideController;
use Illuminate\Support\Facades\Route;

// Consultas con las credenciales PIDE de la municipalidad: solo quien registra personas
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:expedientes|licencias|itse|admin'])->prefix('api/lf-itse')->group(function () {
    // ── PIDE: RENIEC ─────────────────────────────────────────────────────────
    Route::get('reniec/consultar/', [PideController::class, 'reniec'])
        ->name('pide.reniec.consultar');

    // ── PIDE: SUNAT ──────────────────────────────────────────────────────────
    Route::get('sunat/consultar/', [PideController::class, 'sunat'])
        ->name('pide.sunat.consultar');
});
