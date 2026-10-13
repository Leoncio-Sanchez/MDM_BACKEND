<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UnidadesOrganicas\Application\DTOs\GuardarUnidadOrganicaDTO;
use App\Modules\UnidadesOrganicas\Application\UseCases\ActualizarUnidadOrganicaUseCase;
use App\Modules\UnidadesOrganicas\Application\UseCases\CrearUnidadOrganicaUseCase;
use App\Modules\UnidadesOrganicas\Application\UseCases\EliminarUnidadOrganicaUseCase;
use App\Modules\UnidadesOrganicas\Application\UseCases\ListarUnidadesOrganicasUseCase;
use App\Modules\UnidadesOrganicas\Application\UseCases\ObtenerUnidadOrganicaUseCase;
use App\Modules\UnidadesOrganicas\Presentation\Requests\GuardarUnidadOrganicaRequest;
use App\Modules\UnidadesOrganicas\Presentation\Resources\UnidadOrganicaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class UnidadOrganicaController extends Controller
{
    public function __construct(
        private readonly ListarUnidadesOrganicasUseCase $listarUseCase,
        private readonly ObtenerUnidadOrganicaUseCase $obtenerUseCase,
        private readonly CrearUnidadOrganicaUseCase $crearUseCase,
        private readonly ActualizarUnidadOrganicaUseCase $actualizarUseCase,
        private readonly EliminarUnidadOrganicaUseCase $eliminarUseCase,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(UnidadOrganicaResource::collection($this->listarUseCase->execute())->resolve());
    }

    public function show(int $id): JsonResponse
    {
        return response()->json((new UnidadOrganicaResource($this->obtenerUseCase->execute($id)))->resolve());
    }

    public function store(GuardarUnidadOrganicaRequest $request): JsonResponse
    {
        $unidad = $this->crearUseCase->execute(GuardarUnidadOrganicaDTO::fromArray($request->validated()));

        return response()->json((new UnidadOrganicaResource($unidad))->resolve(), 201);
    }

    public function update(GuardarUnidadOrganicaRequest $request, int $id): JsonResponse
    {
        $unidad = $this->actualizarUseCase->execute($id, GuardarUnidadOrganicaDTO::fromArray($request->validated()));

        return response()->json((new UnidadOrganicaResource($unidad))->resolve());
    }

    public function destroy(int $id): Response
    {
        $this->eliminarUseCase->execute($id);

        return response()->noContent();
    }
}
