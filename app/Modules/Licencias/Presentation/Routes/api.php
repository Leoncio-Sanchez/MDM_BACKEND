<?php

declare(strict_types=1);
use App\Modules\Licencias\Presentation\Controllers\LicenciaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD'])->prefix('api/lf-itse')->group(function () {
    Route::post('licencias-funcionamiento', [LicenciaController::class, 'store']);
    Route::get('licencias-funcionamiento/buscar', [LicenciaController::class, 'buscar']);
    Route::get('licencias-funcionamiento/reporte', [LicenciaController::class, 'reporte']);
    Route::get('licencias-funcionamiento/consulta', [LicenciaController::class, 'consulta']);
    Route::match(['get', 'post'], 'licencias-funcionamiento/verificar-expediente', [LicenciaController::class, 'verificarExpediente']);
    Route::post('licencias-funcionamiento/inactivar', [LicenciaController::class, 'inactivar']);
    Route::put('licencias-funcionamiento/{pk}', [LicenciaController::class, 'update'])->whereNumber('pk');
    Route::delete('licencias-funcionamiento/{pk}', [LicenciaController::class, 'destroy'])->whereNumber('pk');
    Route::match(['post', 'patch'], 'licencias-funcionamiento/{pk}/notificacion', [LicenciaController::class, 'notificar'])->whereNumber('pk');
    Route::get('licencias-funcionamiento/{pk}/estados', [LicenciaController::class, 'listarEstados'])->whereNumber('pk');
    Route::get('licencias-funcionamiento/{pk}/giros', [LicenciaController::class, 'giros'])->whereNumber('pk');
});
