<?php

declare(strict_types=1);

use App\Controllers\EstadoController;
use App\Controllers\GiroController;
use App\Controllers\NivelRiesgoController;
use App\Controllers\TipoDocumentoIdentidadController;
use App\Controllers\TipoLicenciaController;
use App\Controllers\TipoProcedimientoTupaController;
use App\Controllers\UnidadOrganicaController;
use App\Controllers\ZonificacionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('lf-itse')->group(function (): void {
    Route::get('fecha-servidor', function () {
        return response()->json([
            'fecha' => now()->setTimezone('America/Lima')->format('Y-m-d'),
        ]);
    });

    Route::get('estados/inactivos-itse', [EstadoController::class, 'inactivosItse']);
    Route::get('estados/inactivos-lf', [EstadoController::class, 'inactivosLf']);
    Route::get('estados', [EstadoController::class, 'index']);
    Route::get('estados/{id}', [EstadoController::class, 'show'])->whereNumber('id');
    Route::post('estados', [EstadoController::class, 'store']);
    Route::put('estados/{id}', [EstadoController::class, 'update'])->whereNumber('id');
    Route::delete('estados/{id}', [EstadoController::class, 'destroy'])->whereNumber('id');

    Route::apiResource('giros', GiroController::class);
    Route::apiResource('tipos-licencia', TipoLicenciaController::class)->parameters([
        'tipos-licencia' => 'id',
    ]);
    Route::apiResource('niveles-riesgo', NivelRiesgoController::class)->parameters([
        'niveles-riesgo' => 'id',
    ]);
    Route::apiResource('zonificaciones', ZonificacionController::class)->parameters([
        'zonificaciones' => 'id',
    ]);
    Route::apiResource('tipos-documento-identidad', TipoDocumentoIdentidadController::class)->parameters([
        'tipos-documento-identidad' => 'id',
    ]);
    Route::apiResource('tipos-procedimiento-tupa', TipoProcedimientoTupaController::class)->parameters([
        'tipos-procedimiento-tupa' => 'id',
    ]);
    Route::get('unidades-organicas', [UnidadOrganicaController::class, 'index']);
    Route::get('unidades-organicas/', [UnidadOrganicaController::class, 'index']);
});
