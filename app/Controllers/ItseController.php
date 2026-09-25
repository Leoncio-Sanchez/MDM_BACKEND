<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Requests\Itse\ActualizarItseRequest;
use App\Requests\Itse\BuscarItseRequest;
use App\Requests\Itse\ConsultaItseRequest;
use App\Requests\Itse\CrearItseRequest;
use App\Requests\Itse\InactivarItseRequest;
use App\Requests\Itse\NotificarItseRequest;
use App\Requests\Itse\PorRenovarItseRequest;
use App\Requests\Itse\VerificarExpedienteRequest;
use App\Resources\ItseResource;
use App\Services\ItseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ItseController extends Controller
{
    public function __construct(
        private readonly ItseService $itseService,
    ) {}

    public function store(CrearItseRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $itse = $this->itseService->crear(
            $request->validated(),
            $userId !== null ? (int) $userId : null
        );

        return response()->json(['id' => $itse->id], 201);
    }

    public function show(int $pk): JsonResponse
    {
        $itse = $this->itseService->obtener($pk);

        return response()->json(
            (new ItseResource($itse))->resolve(),
            200
        );
    }

    public function update(ActualizarItseRequest $request, int $pk): JsonResponse
    {
        $itse = $this->itseService->actualizar($pk, $request->validated());

        return response()->json([
            'id' => $itse->id,
            'mensaje' => 'ITSE modificada correctamente.',
        ], 200);
    }

    public function destroy(int $pk): JsonResponse
    {
        $this->itseService->eliminar($pk);

        return response()->json(null, 204);
    }

    public function buscar(BuscarItseRequest $request): JsonResponse
    {
        $filtro = (string) $request->input('filtro');
        $valor = (string) $request->input('valor');
        $resultados = $this->itseService->buscar($filtro, $valor);

        return response()->json(
            $resultados,
            200,
            [],
            JSON_PRESERVE_ZERO_FRACTION
        );
    }

    public function verificarExpediente(VerificarExpedienteRequest $request): JsonResponse
    {
        $numero = (int) $request->input('numero_expediente');
        $anio = (int) $request->input('anio');
        $resultado = $this->itseService->verificarExpediente($numero, $anio);

        return response()->json($resultado, 200);
    }

    public function inactivar(InactivarItseRequest $request): JsonResponse
    {
        $itseId = (int) $request->input('itse_id');
        $userId = $request->user()?->id;
        $resultado = $this->itseService->inactivar(
            $itseId,
            $request->validated(),
            $userId !== null ? (int) $userId : null
        );

        return response()->json($resultado, 201);
    }

    public function notificacion(NotificarItseRequest $request, int $pk): JsonResponse
    {
        $fecha = (string) $request->input('fecha_notificacion');
        $this->itseService->notificar($pk, $fecha);

        return response()->json([
            'mensaje' => 'Fecha de notificación registrada correctamente.',
        ], 200);
    }

    public function estados(int $pk): JsonResponse
    {
        $estados = $this->itseService->listarEstados($pk);

        return response()->json($estados, 200);
    }

    public function giros(int $pk): JsonResponse
    {
        $giros = $this->itseService->listarGiros($pk);

        return response()->json($giros, 200);
    }

    public function inspectores(int $pk): JsonResponse
    {
        $inspectores = $this->itseService->inspectores($pk);

        return response()->json($inspectores, 200);
    }

    public function asignarInspector(Request $request, int $pk): JsonResponse
    {
        return response()->json(['mensaje' => 'Inspector asignado.'], 201);
    }

    public function porRenovar(PorRenovarItseRequest $request): JsonResponse
    {
        $fechaDesde = $request->input('fecha_desde') !== null ? (string) $request->input('fecha_desde') : null;
        $fechaHasta = $request->input('fecha_hasta') !== null ? (string) $request->input('fecha_hasta') : null;
        $resultados = $this->itseService->porRenovar($fechaDesde, $fechaHasta);

        return response()->json(
            ItseResource::collection($resultados)->resolve(),
            200
        );
    }

    public function consulta(ConsultaItseRequest $request): JsonResponse
    {
        $resultados = $this->itseService->consulta($request->validated());

        return response()->json(
            ItseResource::collection($resultados)->resolve(),
            200
        );
    }
}
