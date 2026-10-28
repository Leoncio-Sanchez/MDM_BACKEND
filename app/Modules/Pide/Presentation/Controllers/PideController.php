<?php

declare(strict_types=1);

namespace App\Modules\Pide\Presentation\Controllers;

use App\Modules\Pide\Application\DTOs\ConsultaReniecDTO;
use App\Modules\Pide\Application\DTOs\ConsultaSunatDTO;
use App\Modules\Pide\Application\UseCases\ConsultarReniecUseCase;
use App\Modules\Pide\Application\UseCases\ConsultarSunatUseCase;
use App\Modules\Pide\Domain\Exceptions\DocumentoNoEncontrado;
use App\Modules\Pide\Domain\Exceptions\ReniecError;
use App\Modules\Pide\Domain\Exceptions\SunatError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

final readonly class PideController
{
    /** El detalle del error queda en el log (report); al usuario no se le muestra. */
    private const ERROR_INESPERADO = 'Ocurrió un error inesperado al consultar el servicio. Intente nuevamente.';

    public function __construct(
        private ConsultarReniecUseCase $consultarReniec,
        private ConsultarSunatUseCase $consultarSunat,
    ) {}

    public function reniec(Request $request): JsonResponse
    {
        $dni = (string) $request->query('dni', '');

        if ($dni === '') {
            return response()->json(
                ['error' => "El parámetro 'dni' es obligatorio."],
                400,
            );
        }

        try {
            $resultado = $this->consultarReniec->execute(
                ConsultaReniecDTO::fromArray(['dni' => $dni]),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (DocumentoNoEncontrado $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (ReniecError $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => self::ERROR_INESPERADO], 500);
        }

        return response()->json($resultado->toArray(), 200);
    }

    public function sunat(Request $request): JsonResponse
    {
        $ruc = (string) $request->query('ruc', '');

        if ($ruc === '') {
            return response()->json(
                ['error' => "El parámetro 'ruc' es obligatorio."],
                400,
            );
        }

        try {
            $resultado = $this->consultarSunat->execute(
                ConsultaSunatDTO::fromArray(['ruc' => $ruc]),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (DocumentoNoEncontrado $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (SunatError $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => self::ERROR_INESPERADO], 500);
        }

        return response()->json($resultado->toArray(), 200);
    }
}
