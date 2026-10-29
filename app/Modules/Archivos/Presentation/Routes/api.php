<?php

declare(strict_types=1);

use App\Modules\Archivos\Presentation\Controllers\ExpedienteArchivoController;
use App\Modules\Archivos\Presentation\Controllers\ItseArchivoController;
use App\Modules\Archivos\Presentation\Controllers\LicenciaFuncionamientoArchivoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD'])->prefix('api/lf-itse')->group(function () {
    // ═══════════════════════════════════════════════════════════════════════════
    // EXPEDIENTES (listar, subir, inactivar, descargar)
    // ═══════════════════════════════════════════════════════════════════════════
    Route::get('expedientes/{pk}/archivos/', [ExpedienteArchivoController::class, 'listar'])
        ->whereNumber('pk')
        ->name('archivos.expedientes.listar')
        ->middleware('permiso:expedientes|licencias|itse,expedientes');

    Route::post('expedientes/{pk}/archivos/', [ExpedienteArchivoController::class, 'subir'])
        ->whereNumber('pk')
        ->name('archivos.expedientes.subir')
        ->middleware('permiso:expedientes|licencias|itse,expedientes');

    // Los adjuntos no se borran: se dan de baja con motivo (auditoría del OCI)
    Route::post('expedientes/archivos/{pk}/inactivar/', [ExpedienteArchivoController::class, 'inactivar'])
        ->whereNumber('pk')
        ->name('archivos.expedientes.inactivar')
        ->middleware('permiso:expedientes|licencias|itse,expedientes');

    Route::get('expedientes/archivos/{uuid}/descargar/', [ExpedienteArchivoController::class, 'descargar'])
        ->name('archivos.expedientes.descargar')
        ->middleware('permiso:expedientes|licencias|itse,expedientes');

    // ═══════════════════════════════════════════════════════════════════════════
    // ITSE (listar, subir, inactivar, descargar)
    // ═══════════════════════════════════════════════════════════════════════════
    Route::get('itse/{pk}/archivos/', [ItseArchivoController::class, 'listar'])
        ->whereNumber('pk')
        ->name('archivos.itse.listar')
        ->middleware('permiso:itse');

    Route::post('itse/{pk}/archivos/', [ItseArchivoController::class, 'subir'])
        ->whereNumber('pk')
        ->name('archivos.itse.subir')
        ->middleware('permiso:itse');

    // Los adjuntos no se borran: se dan de baja con motivo (auditoría del OCI)
    Route::post('itse/archivos/{pk}/inactivar/', [ItseArchivoController::class, 'inactivar'])
        ->whereNumber('pk')
        ->name('archivos.itse.inactivar')
        ->middleware('permiso:itse');

    Route::get('itse/archivos/{uuid}/descargar/', [ItseArchivoController::class, 'descargar'])
        ->name('archivos.itse.descargar')
        ->middleware('permiso:itse');

    // ═══════════════════════════════════════════════════════════════════════════
    // LICENCIAS FUNCIONAMIENTO (listar, subir, inactivar, descargar)
    // ═══════════════════════════════════════════════════════════════════════════
    Route::get('licencias-funcionamiento/{pk}/archivos/', [LicenciaFuncionamientoArchivoController::class, 'listar'])
        ->whereNumber('pk')
        ->name('archivos.licencias.listar')
        ->middleware('permiso:licencias');

    Route::post('licencias-funcionamiento/{pk}/archivos/', [LicenciaFuncionamientoArchivoController::class, 'subir'])
        ->whereNumber('pk')
        ->name('archivos.licencias.subir')
        ->middleware('permiso:licencias');

    // Los adjuntos no se borran: se dan de baja con motivo (auditoría del OCI)
    Route::post('licencias-funcionamiento/archivos/{pk}/inactivar/', [LicenciaFuncionamientoArchivoController::class, 'inactivar'])
        ->whereNumber('pk')
        ->name('archivos.licencias.inactivar')
        ->middleware('permiso:licencias');

    Route::get('licencias-funcionamiento/archivos/{uuid}/descargar/', [LicenciaFuncionamientoArchivoController::class, 'descargar'])
        ->name('archivos.licencias.descargar')
        ->middleware('permiso:licencias');
});
