<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Requests\Personas\BuscarPersonaRequest;
use App\Requests\Personas\StorePersonaRequest;
use App\Requests\Personas\UpdatePersonaRequest;
use App\Resources\PersonaDocumentoDetalleResource;
use App\Resources\PersonaResource;
use App\Services\PersonaService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PersonaController
{
    public function __construct(
        private readonly PersonaService $service,
    ) {}

    public function index(): JsonResponse
    {
        $personas = $this->service->listar();

        return response()->json(PersonaResource::collection($personas)->resolve());
    }

    public function store(StorePersonaRequest $request): JsonResponse
    {
        $userId = (int) ($request->user()?->getAuthIdentifier() ?? 1);
        $persona = $this->service->crear($request->validated(), $userId);

        return response()->json(new PersonaResource($persona), 201);
    }

    public function show(int $pk): JsonResponse
    {
        $persona = $this->service->obtener($pk);

        return response()->json(new PersonaResource($persona));
    }

    public function update(UpdatePersonaRequest $request, int $pk): JsonResponse
    {
        $persona = $this->service->actualizar($pk, $request->validated());

        return response()->json(new PersonaResource($persona));
    }

    public function destroy(int $pk): SymfonyResponse
    {
        $this->service->eliminar($pk);

        return response()->noContent();
    }

    public function buscar(BuscarPersonaRequest $request): JsonResponse
    {
        /** @var string $filtro */
        $filtro = $request->query('filtro') ?? $request->input('filtro');
        /** @var string $valor */
        $valor = $request->query('valor') ?? $request->input('valor');

        $resultados = $this->service->buscar($filtro, $valor);

        return response()->json($resultados);
    }

    public function sexos(): JsonResponse
    {
        return response()->json($this->service->sexos());
    }

    public function documentos(int $pk): JsonResponse
    {
        $documentos = $this->service->documentos($pk);

        return response()->json(PersonaDocumentoDetalleResource::collection($documentos)->resolve());
    }
}
