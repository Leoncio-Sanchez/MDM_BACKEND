<?php

declare(strict_types=1);

use App\Modules\Expedientes\Presentation\Controllers\ExpedienteController;
use Illuminate\Support\Facades\Route;

// Expedientes en riesgo de plazo (reporte): también para "reportes completos" (solo lectura)
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:expedientes|licencias|itse|reportes'])->prefix('api/lf-itse')->group(function () {
    Route::get('expedientes/pendientes/', [ExpedienteController::class, 'pendientes'])
        ->name('expedientes.pendientes');
});

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:expedientes|licencias|itse,expedientes'])->prefix('api/lf-itse')->group(function () {
    // ── 7A Estáticas ──────────────────────────────────────────────────────────
    Route::get('expedientes/', [ExpedienteController::class, 'index']);
    Route::post('expedientes/', [ExpedienteController::class, 'store']);

    // ── 7B Endpoints SQL crudo + ORM ─────────────────────────────────────────
    Route::get('expedientes/buscar/', [ExpedienteController::class, 'buscar'])
        ->name('expedientes.buscar');
    Route::get('expedientes/consulta/', [ExpedienteController::class, 'consulta'])
        ->name('expedientes.consulta');

    // ── 7C Workflows ─────────────────────────────────────────────────────────
    Route::get(
        'expedientes/{pk}/autorizacion-improcedente/',
        [ExpedienteController::class, 'autorizacionImprocedente'],
    )->whereNumber('pk')->name('expedientes.autorizacion-improcedente');

    Route::post(
        'expedientes/{pk}/ampliacion-plazo/',
        [ExpedienteController::class, 'ampliacionPlazo'],
    )->whereNumber('pk')->name('expedientes.ampliacion-plazo');

    // Los expedientes no se eliminan (LPAG, Ley 27444): se anulan con motivo, fecha y usuario
    Route::post(
        'expedientes/{pk}/anular/',
        [ExpedienteController::class, 'anular'],
    )->whereNumber('pk')->name('expedientes.anular');

    // ── 7A Dinámicas ──────────────────────────────────────────────────────────
    Route::get('expedientes/{pk}/', [ExpedienteController::class, 'show'])->whereNumber('pk');
    Route::put('expedientes/{pk}/', [ExpedienteController::class, 'update'])->whereNumber('pk');
});

// Cada denegatoria la registra la oficina que evalúa esa autorización (no mesa de partes)
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:licencias'])->prefix('api/lf-itse')->group(function () {
    Route::post(
        'expedientes/{pk}/denegar-licencia/',
        [ExpedienteController::class, 'denegarLicencia'],
    )->whereNumber('pk')->name('expedientes.denegar-licencia');
});

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:itse'])->prefix('api/lf-itse')->group(function () {
    Route::post(
        'expedientes/{pk}/denegar-itse/',
        [ExpedienteController::class, 'denegarItse'],
    )->whereNumber('pk')->name('expedientes.denegar-itse');
});
