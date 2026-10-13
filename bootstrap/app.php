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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

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
