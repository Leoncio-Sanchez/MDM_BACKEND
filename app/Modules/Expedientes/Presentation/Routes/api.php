<?php
declare(strict_types=1);

use App\Modules\Expedientes\Presentation\Controllers\ExpedienteController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('api/lf-itse')->group(function () {
    // ── 7A Estáticas ──────────────────────────────────────────────────────────
    Route::get('expedientes/', [ExpedienteController::class, 'index']);
    Route::post('expedientes/', [ExpedienteController::class, 'store']);

    // ── 7A Dinámicas ──────────────────────────────────────────────────────────
    Route::get('expedientes/{pk}/', [ExpedienteController::class, 'show'])->whereNumber('pk');
    Route::put('expedientes/{pk}/', [ExpedienteController::class, 'update'])->whereNumber('pk');
    Route::delete('expedientes/{pk}/', [ExpedienteController::class, 'destroy'])->whereNumber('pk');
});
