<?php

declare(strict_types=1);

use App\Controllers\EstadoController;
use App\Controllers\GiroController;
use App\Controllers\InspectorController;
use App\Controllers\LicenciaController;
use App\Controllers\NivelRiesgoController;
use App\Controllers\TipoDocumentoIdentidadController;
use App\Controllers\TipoLicenciaController;
use App\Controllers\TipoProcedimientoTupaController;
use App\Controllers\UnidadOrganicaController;
use App\Controllers\UsuarioController;
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

    Route::get('inspectores/buscar', [InspectorController::class, 'buscar']);
    Route::get('inspectores/buscar/', [InspectorController::class, 'buscar']);
    Route::apiResource('inspectores', InspectorController::class)->parameters([
        'inspectores' => 'pk',
    ]);

    Route::get('licencias-funcionamiento/consulta', [LicenciaController::class, 'consulta']);
    Route::get('licencias-funcionamiento/consulta/', [LicenciaController::class, 'consulta']);
    Route::get('licencias-funcionamiento/buscar', [LicenciaController::class, 'buscar']);
    Route::get('licencias-funcionamiento/buscar/', [LicenciaController::class, 'buscar']);
    Route::get('licencias-funcionamiento/reporte', [LicenciaController::class, 'reporte']);
    Route::get('licencias-funcionamiento/reporte/', [LicenciaController::class, 'reporte']);
    Route::match(['get', 'post'], 'licencias-funcionamiento/verificar-expediente', [LicenciaController::class, 'verificarExpediente']);
    Route::match(['get', 'post'], 'licencias-funcionamiento/verificar-expediente/', [LicenciaController::class, 'verificarExpediente']);
    Route::post('licencias-funcionamiento/inactivar', [LicenciaController::class, 'inactivar']);
    Route::post('licencias-funcionamiento/inactivar/', [LicenciaController::class, 'inactivar']);

    Route::get('licencias-funcionamiento/{pk}/estados', [LicenciaController::class, 'listarEstados'])->whereNumber('pk');
    Route::get('licencias-funcionamiento/{pk}/estados/', [LicenciaController::class, 'listarEstados'])->whereNumber('pk');
    Route::get('licencias-funcionamiento/{pk}/giros', [LicenciaController::class, 'giros'])->whereNumber('pk');
    Route::get('licencias-funcionamiento/{pk}/giros/', [LicenciaController::class, 'giros'])->whereNumber('pk');
    Route::match(['post', 'patch'], 'licencias-funcionamiento/{pk}/notificacion', [LicenciaController::class, 'notificar'])->whereNumber('pk');
    Route::match(['post', 'patch'], 'licencias-funcionamiento/{pk}/notificacion/', [LicenciaController::class, 'notificar'])->whereNumber('pk');

    Route::post('licencias-funcionamiento', [LicenciaController::class, 'store']);
    Route::post('licencias-funcionamiento/', [LicenciaController::class, 'store']);
    Route::get('licencias-funcionamiento/{pk}', [LicenciaController::class, 'show'])->whereNumber('pk');
    Route::get('licencias-funcionamiento/{pk}/', [LicenciaController::class, 'show'])->whereNumber('pk');
    Route::put('licencias-funcionamiento/{pk}', [LicenciaController::class, 'update'])->whereNumber('pk');
    Route::put('licencias-funcionamiento/{pk}/', [LicenciaController::class, 'update'])->whereNumber('pk');
    Route::delete('licencias-funcionamiento/{pk}', [LicenciaController::class, 'destroy'])->whereNumber('pk');
    Route::delete('licencias-funcionamiento/{pk}/', [LicenciaController::class, 'destroy'])->whereNumber('pk');

    Route::get('usuarios/menus', [UsuarioController::class, 'menus']);
    Route::get('usuarios/menus/', [UsuarioController::class, 'menus']);
    Route::get('usuarios/{pk}/detalle', [UsuarioController::class, 'detalle'])->whereNumber('pk');
    Route::get('usuarios/{pk}/detalle/', [UsuarioController::class, 'detalle'])->whereNumber('pk');
    Route::patch('usuarios/{pk}/cambiar-password', [UsuarioController::class, 'cambiarPassword'])->whereNumber('pk');
    Route::patch('usuarios/{pk}/cambiar-password/', [UsuarioController::class, 'cambiarPassword'])->whereNumber('pk');
    Route::apiResource('usuarios', UsuarioController::class)->parameters([
        'usuarios' => 'pk',
    ]);
});
