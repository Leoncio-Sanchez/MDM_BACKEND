<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Requests\Expedientes\AmpliarPlazoRequest;
use App\Requests\Expedientes\BuscarExpedienteRequest;
use App\Requests\Expedientes\DenegarItseRequest;
use App\Requests\Expedientes\DenegarLicenciaRequest;
use App\Requests\Expedientes\StoreExpedienteRequest;
use App\Requests\Expedientes\UpdateExpedienteRequest;
use App\Resources\AutorizacionImprocedenteResource;
use App\Resources\ExpedienteBusquedaResource;
use App\Resources\ExpedienteListResource;
use App\Resources\ExpedientePendienteResource;
use App\Resources\ExpedienteResource;
use App\Services\ExpedienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ExpedienteController extends Controller
{
    public function __construct(
        private readonly ExpedienteService $expedienteService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);
        $paginator = $this->expedienteService->listar($page, $perPage);

        return response()->json(
            ExpedienteListResource::collection($paginator->items())->resolve(),
            200
        );
    }

    public function store(StoreExpedienteRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $expediente = $this->expedienteService->crear(
            $request->validated(),
            $userId !== null ? (int) $userId : null
        );

        return response()->json(
            (new ExpedienteResource($expediente))->resolve(),
            201
        );
    }

    public function show(int $pk): JsonResponse
    {
        $expediente = $this->expedienteService->obtener($pk);

        return response()->json(
            (new ExpedienteResource($expediente))->resolve(),
            200
        );
    }

    public function update(UpdateExpedienteRequest $request, int $pk): JsonResponse
    {
        $expediente = $this->expedienteService->actualizar($pk, $request->validated());

        return response()->json(
            (new ExpedienteResource($expediente))->resolve(),
            200
        );
    }

    public function destroy(int $pk): JsonResponse
    {
        $this->expedienteService->eliminar($pk);

        return response()->json(null, 204);
    }

    public function pendientes(): JsonResponse
    {
        $filas = $this->expedienteService->listarPendientes();

        return response()->json(
            ExpedientePendienteResource::collection($filas)->resolve(),
            200
        );
    }

    public function buscar(BuscarExpedienteRequest $request): JsonResponse
    {
        $filtro = (string) $request->input('filtro');
        $valor = (string) $request->input('valor');
        $filas = $this->expedienteService->buscar($filtro, $valor);

        return response()->json(
            ExpedienteBusquedaResource::collection($filas)->resolve(),
            200
        );
    }

    public function consulta(Request $request): JsonResponse
    {
        $expedientes = $this->expedienteService->consulta($request->all());

        return response()->json(
            ExpedienteResource::collection($expedientes)->resolve(),
            200
        );
    }

    public function ampliarPlazo(AmpliarPlazoRequest $request, int $pk): JsonResponse
    {
        $userId = $request->user()?->id;
        $expediente = $this->expedienteService->ampliarPlazo(
            $pk,
            $request->validated(),
            $userId !== null ? (int) $userId : null
        );

        return response()->json(
            (new ExpedienteResource($expediente))->resolve(),
            200
        );
    }

    public function denegarLicencia(DenegarLicenciaRequest $request, int $pk): JsonResponse
    {
        $userId = $request->user()?->id;
        $autorizacion = $this->expedienteService->denegarLicencia(
            $pk,
            $request->validated(),
            $userId !== null ? (int) $userId : null
        );

        return response()->json(
            (new AutorizacionImprocedenteResource($autorizacion))->resolve(),
            201
        );
    }

    public function denegarItse(DenegarItseRequest $request, int $pk): JsonResponse
    {
        $userId = $request->user()?->id;
        $autorizacion = $this->expedienteService->denegarItse(
            $pk,
            $request->validated(),
            $userId !== null ? (int) $userId : null
        );

        return response()->json(
            (new AutorizacionImprocedenteResource($autorizacion))->resolve(),
            201
        );
    }

    public function autorizacionImprocedente(Request $request, int $pk): JsonResponse
    {
        $tipo = (string) $request->query('tipo', '');
        if ($tipo === '') {
            return response()->json(
                ['error' => "El parámetro 'tipo' es requerido ('LF' o 'ITSE')."],
                400
            );
        }

        $autorizacion = $this->expedienteService->buscarAutorizacion($pk, $tipo);

        return response()->json(
            $autorizacion ? (new AutorizacionImprocedenteResource($autorizacion))->resolve() : null,
            200
        );
    }
}
