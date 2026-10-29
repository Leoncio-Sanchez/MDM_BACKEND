<?php

declare(strict_types=1);

use App\Modules\Inspecciones\Presentation\Controllers\ActaInspeccionController;
use App\Modules\Inspecciones\Presentation\Controllers\AsignacionInspeccionController;
use Illuminate\Support\Facades\Route;

// Jefe de ITSE: asigna, reprograma y reasigna inspectores
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:jefe_itse'])->prefix('api/lf-itse/inspecciones')->group(function () {
    Route::get('por-inspeccionar/', [AsignacionInspeccionController::class, 'porInspeccionar']);
    Route::get('inspectores/', [AsignacionInspeccionController::class, 'inspectores']);
    Route::post('asignaciones/', [AsignacionInspeccionController::class, 'asignar']);
    Route::put('asignaciones/{pk}/', [AsignacionInspeccionController::class, 'actualizar'])->whereNumber('pk');
    Route::post('asignaciones/{pk}/reasignar/', [AsignacionInspeccionController::class, 'reasignar'])->whereNumber('pk');
    Route::get('expedientes/{expedienteId}/historial/', [AsignacionInspeccionController::class, 'historial'])->whereNumber('expedienteId');
});

// Actas (solo lectura): la oficina de ITSE las necesita para emitir el certificado o denegarlo
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:itse|jefe_itse'])->prefix('api/lf-itse/inspecciones')->group(function () {
    Route::get('expedientes/{expedienteId}/actas/', [ActaInspeccionController::class, 'deExpediente'])->whereNumber('expedienteId');
    Route::get('actas/{actaId}/', [ActaInspeccionController::class, 'ver'])->whereNumber('actaId');
    Route::get('actas/{actaId}/fotos/{fotoId}/', [ActaInspeccionController::class, 'foto'])->whereNumber(['actaId', 'fotoId']);
});

// Inspector de campo: solo sus inspecciones. También el personal marcado como inspector.
Route::middleware(['auth:api', 'rol:INSPECTOR,ADMIN_MUNICIPALIDAD', 'permiso:inspector'])->prefix('api/lf-itse/inspecciones')->group(function () {
    Route::get('mi-bandeja/', [AsignacionInspeccionController::class, 'miBandeja']);
    Route::post('mi-bandeja/{asignacionId}/acta/', [ActaInspeccionController::class, 'registrar'])->whereNumber('asignacionId');
    Route::get('mi-bandeja/actas/{actaId}/', [ActaInspeccionController::class, 'verComoInspector'])->whereNumber('actaId');
    Route::get('mi-bandeja/actas/{actaId}/fotos/{fotoId}/', [ActaInspeccionController::class, 'fotoComoInspector'])->whereNumber(['actaId', 'fotoId']);
});
