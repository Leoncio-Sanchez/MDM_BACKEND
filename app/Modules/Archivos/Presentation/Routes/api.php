<?php

declare(strict_types=1);

use App\Modules\Archivos\Presentation\Controllers\ExpedienteArchivoController;
use App\Modules\Archivos\Presentation\Controllers\ItseArchivoController;
use App\Modules\Archivos\Presentation\Controllers\LicenciaFuncionamientoArchivoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('api/lf-itse')->group(function () {
    // ═══════════════════════════════════════════════════════════════════════════
    // EXPEDIENTES (3 rutas, 4 operaciones)
    // ═══════════════════════════════════════════════════════════════════════════
    Route::get('expedientes/{pk}/archivos/', [ExpedienteArchivoController::class, 'listar'])
        ->whereNumber('pk')
        ->name('archivos.expedientes.listar');

    Route::post('expedientes/{pk}/archivos/', [ExpedienteArchivoController::class, 'subir'])
        ->whereNumber('pk')
        ->name('archivos.expedientes.subir');

    Route::delete('expedientes/archivos/{pk}/', [ExpedienteArchivoController::class, 'eliminar'])
        ->whereNumber('pk')
        ->name('archivos.expedientes.eliminar');

    Route::get('expedientes/archivos/{uuid}/descargar/', [ExpedienteArchivoController::class, 'descargar'])
        ->name('archivos.expedientes.descargar');

    // ═══════════════════════════════════════════════════════════════════════════
    // ITSE (3 rutas, 4 operaciones)
    // ═══════════════════════════════════════════════════════════════════════════
    Route::get('itse/{pk}/archivos/', [ItseArchivoController::class, 'listar'])
        ->whereNumber('pk')
        ->name('archivos.itse.listar');

    Route::post('itse/{pk}/archivos/', [ItseArchivoController::class, 'subir'])
        ->whereNumber('pk')
        ->name('archivos.itse.subir');

    Route::delete('itse/archivos/{pk}/', [ItseArchivoController::class, 'eliminar'])
        ->whereNumber('pk')
        ->name('archivos.itse.eliminar');

    Route::get('itse/archivos/{uuid}/descargar/', [ItseArchivoController::class, 'descargar'])
        ->name('archivos.itse.descargar');

    // ═══════════════════════════════════════════════════════════════════════════
    // LICENCIAS FUNCIONAMIENTO (3 rutas, 4 operaciones)
    // ═══════════════════════════════════════════════════════════════════════════
    Route::get('licencias-funcionamiento/{pk}/archivos/', [LicenciaFuncionamientoArchivoController::class, 'listar'])
        ->whereNumber('pk')
        ->name('archivos.licencias.listar');

    Route::post('licencias-funcionamiento/{pk}/archivos/', [LicenciaFuncionamientoArchivoController::class, 'subir'])
        ->whereNumber('pk')
        ->name('archivos.licencias.subir');

    Route::delete('licencias-funcionamiento/archivos/{pk}/', [LicenciaFuncionamientoArchivoController::class, 'eliminar'])
        ->whereNumber('pk')
        ->name('archivos.licencias.eliminar');

    Route::get('licencias-funcionamiento/archivos/{uuid}/descargar/', [LicenciaFuncionamientoArchivoController::class, 'descargar'])
        ->name('archivos.licencias.descargar');
});
