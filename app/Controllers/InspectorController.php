<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Requests\Inspectores\BuscarInspectorRequest;
use App\Requests\Inspectores\StoreInspectorRequest;
use App\Requests\Inspectores\UpdateInspectorRequest;
use App\Resources\InspectorResource;
use App\Services\InspectorService;
use Illuminate\Http\JsonResponse;

final class InspectorController extends Controller
{
    public function __construct(
        private readonly InspectorService $inspectorService,
    ) {}

    public function index(BuscarInspectorRequest $request): JsonResponse
    {
        $busqueda = $request->query('busqueda');
        $inspectores = $this->inspectorService->listar(is_string($busqueda) ? $busqueda : null);

        return response()->json(
            InspectorResource::collection($inspectores)->resolve()
        );
    }

    public function buscar(BuscarInspectorRequest $request): JsonResponse
    {
        return $this->index($request);
    }

    public function store(StoreInspectorRequest $request): JsonResponse
    {
        $inspector = $this->inspectorService->crear($request->validated());

        return response()->json((new InspectorResource($inspector))->resolve(), 201);
    }

    public function show(int $pk): JsonResponse
    {
        $inspector = $this->inspectorService->obtenerPorId($pk);
        if (! $inspector) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json((new InspectorResource($inspector))->resolve(), 200);
    }

    public function update(UpdateInspectorRequest $request, int $pk): JsonResponse
    {
        $inspector = $this->inspectorService->actualizar($pk, $request->validated());
        if (! $inspector) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json((new InspectorResource($inspector))->resolve(), 200);
    }

    public function destroy(int $pk): JsonResponse
    {
        $exito = $this->inspectorService->eliminar($pk);
        if (! $exito) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json(null, 204);
    }
}
