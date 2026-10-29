<?php

declare(strict_types=1);

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

// Inspector de campo: solo sus inspecciones. También el personal marcado como inspector.
Route::middleware(['auth:api', 'rol:INSPECTOR,ADMIN_MUNICIPALIDAD', 'permiso:inspector'])->prefix('api/lf-itse/inspecciones')->group(function () {
    Route::get('mi-bandeja/', [AsignacionInspeccionController::class, 'miBandeja']);
});
