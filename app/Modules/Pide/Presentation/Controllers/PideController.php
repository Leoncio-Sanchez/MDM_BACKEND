<?php

declare(strict_types=1);

namespace App\Modules\Pide\Presentation\Controllers;

use App\Modules\Pide\Application\DTOs\ConsultaReniecDTO;
use App\Modules\Pide\Application\DTOs\ConsultaSunatDTO;
use App\Modules\Pide\Application\UseCases\ConsultarReniecUseCase;
use App\Modules\Pide\Application\UseCases\ConsultarSunatUseCase;
use App\Modules\Pide\Domain\Exceptions\ReniecError;
use App\Modules\Pide\Domain\Exceptions\SunatError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

final readonly class PideController
{
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
        } catch (ReniecError $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($resultado, 200);
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
        } catch (SunatError $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($resultado, 200);
    }
}
