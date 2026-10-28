<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rol' => \App\Modules\Seguridad\Presentation\Middleware\VerificarRol::class,
            'permiso' => \App\Modules\Seguridad\Presentation\Middleware\VerificarPermiso::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\App\Modules\ConsultaPublica\Domain\Exceptions\TramiteNoEncontrado $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 404);
        });

        // Trigger trg_itse_aforo_maximo (Corte 2): la app valida antes, esto es la última defensa
        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) {
            if (($e->errorInfo[0] ?? null) !== 'LF001') {
                return null; // cualquier otro error de BD sigue el manejo normal
            }

            return response()->json(['error' => 'Aforo excedido: la capacidad supera el máximo permitido para el área del local (2 personas por m²).'], 422);
        });

        $exceptions->render(function (\App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 404);
        });

        $exceptions->render(function (\App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 404);
        });

        $exceptions->render(function (\App\Modules\Expedientes\Domain\Exceptions\ExpedienteDuplicadoError $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 409);
        });

        $exceptions->render(function (\App\Modules\Expedientes\Domain\Exceptions\ExpedienteConLicenciaError $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 409);
        });

        $exceptions->render(function (\App\Modules\Expedientes\Domain\Exceptions\ExpedienteConItseError $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 409);
        });

        $exceptions->render(function (\App\Modules\ITSE\Domain\Exceptions\ItseInvalido|\App\Modules\ITSE\Domain\Exceptions\ItseFechaInvalida|\App\Modules\ITSE\Domain\Exceptions\ExpedienteNoValidoItse $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 422);
        });

        // Catálogos: registro inexistente → 404 (antes respondían 500)
        $exceptions->render(function (\App\Modules\Estados\Domain\Exceptions\EstadoNoEncontrado|\App\Modules\Giros\Domain\Exceptions\GiroNoEncontrado|\App\Modules\NivelRiesgo\Domain\Exceptions\NivelRiesgoNoEncontrado|\App\Modules\TipoDocumentoIdentidad\Domain\Exceptions\TipoDocumentoIdentidadNoEncontrado|\App\Modules\TipoLicencia\Domain\Exceptions\TipoLicenciaNoEncontrado|\App\Modules\Zonificacion\Domain\Exceptions\ZonificacionNoEncontrado $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 404);
        });

        $exceptions->render(function (\App\Modules\UnidadesOrganicas\Domain\Exceptions\UnidadOrganicaNoEncontrada $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 404);
        });

        $exceptions->render(function (\App\Modules\UnidadesOrganicas\Domain\Exceptions\UnidadOrganicaEnUso $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 409);
        });

        $exceptions->render(function (\App\Modules\TipoProcedimientoTupa\Domain\Exceptions\TipoProcedimientoTupaNoEncontrado $e, Request $request) {
            return response()->json(['error' => 'El procedimiento TUPA no existe.'], 404);
        });

        $exceptions->render(function (\App\Modules\TipoProcedimientoTupa\Domain\Exceptions\TipoProcedimientoTupaEnUso $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 409);
        });

        $exceptions->render(function (\App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 404);
        });

        $exceptions->render(function (\App\Modules\Licencias\Domain\Exceptions\LicenciaDuplicadaError|\App\Modules\Licencias\Domain\Exceptions\ReciboPagoDuplicadoError|\App\Modules\Licencias\Domain\Exceptions\EstadoInactivacionDuplicadoError|\App\Modules\Licencias\Domain\Exceptions\LicenciaTieneDependientesError|\App\Modules\Licencias\Domain\Exceptions\LicenciaDenegadaError $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 409);
        });

        $exceptions->render(function (\App\Modules\Licencias\Domain\Exceptions\ExpedienteNoValido|\App\Modules\Licencias\Domain\Exceptions\LicenciaInvalida|\App\Modules\Licencias\Domain\Exceptions\NombreComercialInvalido|\App\Modules\Licencias\Domain\Exceptions\NotificacionFechaInvalidaError|\App\Modules\Licencias\Domain\Exceptions\NumeroLicenciaInvalido $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 422);
        });

        $exceptions->render(function (\App\Modules\Personas\Domain\Exceptions\PersonaNoEncontrada $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 404);
        });

        $exceptions->render(function (\App\Modules\Personas\Domain\Exceptions\PersonaTieneDependientes $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 409);
        });

        $exceptions->render(function (\App\Modules\Personas\Domain\Exceptions\DocumentoInvalido|\App\Modules\Personas\Domain\Exceptions\TipoDocumentoNoValido $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 422);
        });
    })->create();
