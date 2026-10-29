<?php
declare(strict_types=1);

use App\Modules\ITSE\Presentation\Controllers\ItseController;
use Illuminate\Support\Facades\Route;

// Reporte de certificados por vencer: también lo consulta quien tiene "reportes completos" (solo lectura)
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:itse|reportes'])->prefix('api/lf-itse')->group(function () {
    Route::get('itse/por-renovar/', [ItseController::class, 'porRenovar']);
});

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:itse'])->prefix('api/lf-itse')->group(function () {
    // ── 6B estáticas (ANTES de las dinámicas con {pk}) ───────────────────────
    Route::get('itse/consulta/', [ItseController::class, 'consulta']);

    // ── 6A estáticas ─────────────────────────────────────────────────────────
    Route::get('itse/buscar/', [ItseController::class, 'buscar']);
    Route::get('itse/verificar-expediente/', [ItseController::class, 'verificarExpediente']);
    Route::post('itse/verificar-expediente/', [ItseController::class, 'verificarExpediente']);
    Route::post('itse/inactivar/', [ItseController::class, 'inactivar']);
    // Acta de inspección que sustenta el certificado (o por qué aún no se puede emitir)
    Route::get('itse/expedientes/{expedienteId}/inspeccion/', [ItseController::class, 'inspeccion'])->whereNumber('expedienteId');

    // ── 6A dinámicas ─────────────────────────────────────────────────────────
    Route::post('itse/', [ItseController::class, 'store']);
    Route::get('itse/{pk}/', [ItseController::class, 'show'])->whereNumber('pk');
    Route::put('itse/{pk}/', [ItseController::class, 'update'])->whereNumber('pk');
    Route::match(['post', 'patch'], 'itse/{pk}/notificacion/', [ItseController::class, 'notificacion'])->whereNumber('pk');

    // ── 6B dinámicas ─────────────────────────────────────────────────────────
    Route::get('itse/{pk}/giros/', [ItseController::class, 'giros'])->whereNumber('pk');
    Route::get('itse/{pk}/estados/', [ItseController::class, 'estados'])->whereNumber('pk');
    Route::get('itse/{pk}/inspectores/', [ItseController::class, 'inspectores'])->whereNumber('pk');
});
