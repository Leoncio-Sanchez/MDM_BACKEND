<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\Pide\ReniecError;
use App\Exceptions\Pide\SunatError;
use App\Requests\Pide\ConsultarReniecRequest;
use App\Requests\Pide\ConsultarSunatRequest;
use App\Services\External\ReniecService;
use App\Services\External\SunatService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;

final class PideController
{
    public function __construct(
        private readonly ReniecService $reniecService,
        private readonly SunatService $sunatService,
    ) {}

    public function reniec(ConsultarReniecRequest $request): JsonResponse
    {
        $dni = trim((string) ($request->query('dni') ?? $request->input('dni', '')));

        if ($dni === '') {
            return response()->json(
                ['error' => "El parámetro 'dni' es obligatorio."],
                400,
            );
        }

        try {
            $resultado = $this->reniecService->consultarPorDni($dni);

            return response()->json($resultado, 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (ReniecError $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sunat(ConsultarSunatRequest $request): JsonResponse
    {
        $ruc = trim((string) ($request->query('ruc') ?? $request->input('ruc', '')));

        if ($ruc === '') {
            return response()->json(
                ['error' => "El parámetro 'ruc' es obligatorio."],
                400,
            );
        }

        try {
            $resultado = $this->sunatService->consultarPorRuc($ruc);

            return response()->json($resultado, 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (SunatError $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
