<?php

declare(strict_types=1);

use App\Controllers\ArchivoController;
use App\Controllers\EstadoController;
use App\Controllers\ExpedienteController;
use App\Controllers\GiroController;
use App\Controllers\InspectorController;
use App\Controllers\ItseController;
use App\Controllers\LicenciaController;
use App\Controllers\NivelRiesgoController;
use App\Controllers\PersonaController;
use App\Controllers\PideController;
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

    Route::get('personas/buscar', [PersonaController::class, 'buscar']);
    Route::get('personas/buscar/', [PersonaController::class, 'buscar']);
    Route::get('personas/sexos', [PersonaController::class, 'sexos']);
    Route::get('personas/sexos/', [PersonaController::class, 'sexos']);
    Route::get('personas/{pk}/documentos', [PersonaController::class, 'documentos'])->whereNumber('pk');
    Route::get('personas/{pk}/documentos/', [PersonaController::class, 'documentos'])->whereNumber('pk');

    Route::get('personas', [PersonaController::class, 'index']);
    Route::get('personas/', [PersonaController::class, 'index']);
    Route::post('personas', [PersonaController::class, 'store']);
    Route::post('personas/', [PersonaController::class, 'store']);
    Route::get('personas/{pk}', [PersonaController::class, 'show'])->whereNumber('pk');
    Route::get('personas/{pk}/', [PersonaController::class, 'show'])->whereNumber('pk');
    Route::put('personas/{pk}', [PersonaController::class, 'update'])->whereNumber('pk');
    Route::put('personas/{pk}/', [PersonaController::class, 'update'])->whereNumber('pk');
    Route::delete('personas/{pk}', [PersonaController::class, 'destroy'])->whereNumber('pk');
    Route::delete('personas/{pk}/', [PersonaController::class, 'destroy'])->whereNumber('pk');

    Route::get('reniec/consultar', [PideController::class, 'reniec'])->name('pide.reniec.consultar');
    Route::get('reniec/consultar/', [PideController::class, 'reniec']);
    Route::get('sunat/consultar', [PideController::class, 'sunat'])->name('pide.sunat.consultar');
    Route::get('sunat/consultar/', [PideController::class, 'sunat']);

    Route::get('inspectores/buscar', [InspectorController::class, 'buscar']);
    Route::get('inspectores/buscar/', [InspectorController::class, 'buscar']);
    Route::apiResource('inspectores', InspectorController::class)->parameters([
        'inspectores' => 'pk',
    ]);

    Route::get('expedientes/pendientes', [ExpedienteController::class, 'pendientes']);
    Route::get('expedientes/pendientes/', [ExpedienteController::class, 'pendientes']);
    Route::get('expedientes/buscar', [ExpedienteController::class, 'buscar']);
    Route::get('expedientes/buscar/', [ExpedienteController::class, 'buscar']);
    Route::get('expedientes/consulta', [ExpedienteController::class, 'consulta']);
    Route::get('expedientes/consulta/', [ExpedienteController::class, 'consulta']);

    Route::post('expedientes/{pk}/ampliacion-plazo', [ExpedienteController::class, 'ampliarPlazo'])->whereNumber('pk');
    Route::post('expedientes/{pk}/ampliacion-plazo/', [ExpedienteController::class, 'ampliarPlazo'])->whereNumber('pk');
    Route::post('expedientes/{pk}/denegar-licencia', [ExpedienteController::class, 'denegarLicencia'])->whereNumber('pk');
    Route::post('expedientes/{pk}/denegar-licencia/', [ExpedienteController::class, 'denegarLicencia'])->whereNumber('pk');
    Route::post('expedientes/{pk}/denegar-itse', [ExpedienteController::class, 'denegarItse'])->whereNumber('pk');
    Route::post('expedientes/{pk}/denegar-itse/', [ExpedienteController::class, 'denegarItse'])->whereNumber('pk');
    Route::get('expedientes/{pk}/autorizacion-improcedente', [ExpedienteController::class, 'autorizacionImprocedente'])->whereNumber('pk');
    Route::get('expedientes/{pk}/autorizacion-improcedente/', [ExpedienteController::class, 'autorizacionImprocedente'])->whereNumber('pk');
    Route::get('expedientes/archivos/{uuid}/descargar', [ArchivoController::class, 'descargarExpediente'])->name('archivos.expedientes.descargar');
    Route::get('expedientes/archivos/{uuid}/descargar/', [ArchivoController::class, 'descargarExpediente']);
    Route::delete('expedientes/archivos/{pk}', [ArchivoController::class, 'eliminarExpediente'])->whereNumber('pk')->name('archivos.expedientes.eliminar');
    Route::delete('expedientes/archivos/{pk}/', [ArchivoController::class, 'eliminarExpediente'])->whereNumber('pk');
    Route::get('expedientes/{pk}/archivos', [ArchivoController::class, 'listarExpediente'])->whereNumber('pk')->name('archivos.expedientes.listar');
    Route::get('expedientes/{pk}/archivos/', [ArchivoController::class, 'listarExpediente'])->whereNumber('pk');
    Route::post('expedientes/{pk}/archivos', [ArchivoController::class, 'subirExpediente'])->whereNumber('pk')->name('archivos.expedientes.subir');
    Route::post('expedientes/{pk}/archivos/', [ArchivoController::class, 'subirExpediente'])->whereNumber('pk');

    Route::get('expedientes', [ExpedienteController::class, 'index']);
    Route::get('expedientes/', [ExpedienteController::class, 'index']);
    Route::post('expedientes', [ExpedienteController::class, 'store']);
    Route::post('expedientes/', [ExpedienteController::class, 'store']);
    Route::get('expedientes/{pk}', [ExpedienteController::class, 'show'])->whereNumber('pk');
    Route::get('expedientes/{pk}/', [ExpedienteController::class, 'show'])->whereNumber('pk');
    Route::put('expedientes/{pk}', [ExpedienteController::class, 'update'])->whereNumber('pk');
    Route::put('expedientes/{pk}/', [ExpedienteController::class, 'update'])->whereNumber('pk');
    Route::delete('expedientes/{pk}', [ExpedienteController::class, 'destroy'])->whereNumber('pk');
    Route::delete('expedientes/{pk}/', [ExpedienteController::class, 'destroy'])->whereNumber('pk');

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
    Route::get('licencias-funcionamiento/archivos/{uuid}/descargar', [ArchivoController::class, 'descargarLicencia'])->name('archivos.licencias.descargar');
    Route::get('licencias-funcionamiento/archivos/{uuid}/descargar/', [ArchivoController::class, 'descargarLicencia']);
    Route::delete('licencias-funcionamiento/archivos/{pk}', [ArchivoController::class, 'eliminarLicencia'])->whereNumber('pk')->name('archivos.licencias.eliminar');
    Route::delete('licencias-funcionamiento/archivos/{pk}/', [ArchivoController::class, 'eliminarLicencia'])->whereNumber('pk');
    Route::get('licencias-funcionamiento/{pk}/archivos', [ArchivoController::class, 'listarLicencia'])->whereNumber('pk')->name('archivos.licencias.listar');
    Route::get('licencias-funcionamiento/{pk}/archivos/', [ArchivoController::class, 'listarLicencia'])->whereNumber('pk');
    Route::post('licencias-funcionamiento/{pk}/archivos', [ArchivoController::class, 'subirLicencia'])->whereNumber('pk')->name('archivos.licencias.subir');
    Route::post('licencias-funcionamiento/{pk}/archivos/', [ArchivoController::class, 'subirLicencia'])->whereNumber('pk');

    Route::post('licencias-funcionamiento', [LicenciaController::class, 'store']);
    Route::post('licencias-funcionamiento/', [LicenciaController::class, 'store']);
    Route::get('licencias-funcionamiento/{pk}', [LicenciaController::class, 'show'])->whereNumber('pk');
    Route::get('licencias-funcionamiento/{pk}/', [LicenciaController::class, 'show'])->whereNumber('pk');
    Route::put('licencias-funcionamiento/{pk}', [LicenciaController::class, 'update'])->whereNumber('pk');
    Route::put('licencias-funcionamiento/{pk}/', [LicenciaController::class, 'update'])->whereNumber('pk');
    Route::delete('licencias-funcionamiento/{pk}', [LicenciaController::class, 'destroy'])->whereNumber('pk');
    Route::delete('licencias-funcionamiento/{pk}/', [LicenciaController::class, 'destroy'])->whereNumber('pk');

    Route::get('itse/por-renovar', [ItseController::class, 'porRenovar']);
    Route::get('itse/por-renovar/', [ItseController::class, 'porRenovar']);
    Route::get('itse/consulta', [ItseController::class, 'consulta']);
    Route::get('itse/consulta/', [ItseController::class, 'consulta']);
    Route::get('itse/buscar', [ItseController::class, 'buscar']);
    Route::get('itse/buscar/', [ItseController::class, 'buscar']);
    Route::match(['get', 'post'], 'itse/verificar-expediente', [ItseController::class, 'verificarExpediente']);
    Route::match(['get', 'post'], 'itse/verificar-expediente/', [ItseController::class, 'verificarExpediente']);
    Route::post('itse/inactivar', [ItseController::class, 'inactivar']);
    Route::post('itse/inactivar/', [ItseController::class, 'inactivar']);

    Route::post('itse', [ItseController::class, 'store']);
    Route::post('itse/', [ItseController::class, 'store']);
    Route::get('itse/{pk}', [ItseController::class, 'show'])->whereNumber('pk');
    Route::get('itse/{pk}/', [ItseController::class, 'show'])->whereNumber('pk');
    Route::put('itse/{pk}', [ItseController::class, 'update'])->whereNumber('pk');
    Route::put('itse/{pk}/', [ItseController::class, 'update'])->whereNumber('pk');
    Route::delete('itse/{pk}', [ItseController::class, 'destroy'])->whereNumber('pk');
    Route::delete('itse/{pk}/', [ItseController::class, 'destroy'])->whereNumber('pk');
    Route::match(['post', 'patch'], 'itse/{pk}/notificacion', [ItseController::class, 'notificacion'])->whereNumber('pk');
    Route::match(['post', 'patch'], 'itse/{pk}/notificacion/', [ItseController::class, 'notificacion'])->whereNumber('pk');
    Route::get('itse/{pk}/estados', [ItseController::class, 'estados'])->whereNumber('pk');
    Route::get('itse/{pk}/estados/', [ItseController::class, 'estados'])->whereNumber('pk');
    Route::get('itse/{pk}/giros', [ItseController::class, 'giros'])->whereNumber('pk');
    Route::get('itse/{pk}/giros/', [ItseController::class, 'giros'])->whereNumber('pk');
    Route::get('itse/{pk}/inspectores', [ItseController::class, 'inspectores'])->whereNumber('pk');
    Route::get('itse/{pk}/inspectores/', [ItseController::class, 'inspectores'])->whereNumber('pk');
    Route::post('itse/{pk}/inspectores', [ItseController::class, 'asignarInspector'])->whereNumber('pk');
    Route::post('itse/{pk}/inspectores/', [ItseController::class, 'asignarInspector'])->whereNumber('pk');
    Route::get('itse/archivos/{uuid}/descargar', [ArchivoController::class, 'descargarItse'])->name('archivos.itse.descargar');
    Route::get('itse/archivos/{uuid}/descargar/', [ArchivoController::class, 'descargarItse']);
    Route::delete('itse/archivos/{pk}', [ArchivoController::class, 'eliminarItse'])->whereNumber('pk')->name('archivos.itse.eliminar');
    Route::delete('itse/archivos/{pk}/', [ArchivoController::class, 'eliminarItse'])->whereNumber('pk');
    Route::get('itse/{pk}/archivos', [ArchivoController::class, 'listarItse'])->whereNumber('pk')->name('archivos.itse.listar');
    Route::get('itse/{pk}/archivos/', [ArchivoController::class, 'listarItse'])->whereNumber('pk');
    Route::post('itse/{pk}/archivos', [ArchivoController::class, 'subirItse'])->whereNumber('pk')->name('archivos.itse.subir');
    Route::post('itse/{pk}/archivos/', [ArchivoController::class, 'subirItse'])->whereNumber('pk');

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
