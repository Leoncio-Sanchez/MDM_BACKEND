<?php
declare(strict_types=1);

use App\Modules\ITSE\Presentation\Controllers\ItseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('api/lf-itse')->group(function () {
    // ── 6B estáticas (ANTES de las dinámicas con {pk}) ───────────────────────
    Route::get('itse/por-renovar/', [ItseController::class, 'porRenovar']);
    Route::get('itse/consulta/', [ItseController::class, 'consulta']);

    // ── 6A estáticas ─────────────────────────────────────────────────────────
    Route::get('itse/buscar/', [ItseController::class, 'buscar']);
    Route::get('itse/verificar-expediente/', [ItseController::class, 'verificarExpediente']);
    Route::post('itse/verificar-expediente/', [ItseController::class, 'verificarExpediente']);
    Route::post('itse/inactivar/', [ItseController::class, 'inactivar']);

    // ── 6A dinámicas ─────────────────────────────────────────────────────────
    Route::post('itse/', [ItseController::class, 'store']);
    Route::get('itse/{pk}/', [ItseController::class, 'show'])->whereNumber('pk');
    Route::put('itse/{pk}/', [ItseController::class, 'update'])->whereNumber('pk');
    Route::post('itse/{pk}/notificacion/', [ItseController::class, 'notificacion'])->whereNumber('pk');

    // ── 6B dinámicas ─────────────────────────────────────────────────────────
    Route::get('itse/{pk}/giros/', [ItseController::class, 'giros'])->whereNumber('pk');
    Route::get('itse/{pk}/estados/', [ItseController::class, 'estados'])->whereNumber('pk');
    Route::get('itse/{pk}/inspectores/', [ItseController::class, 'inspectores'])->whereNumber('pk');
});
