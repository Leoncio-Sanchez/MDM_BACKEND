<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Presentation\Controllers;

use App\Modules\Zonificacion\Application\DTOs\GuardarZonificacionDTO;
use App\Modules\Zonificacion\Application\UseCases\ActualizarZonificacionUseCase;
use App\Modules\Zonificacion\Application\UseCases\CrearZonificacionUseCase;
use App\Modules\Zonificacion\Application\UseCases\EliminarZonificacionUseCase;
use App\Modules\Zonificacion\Application\UseCases\ListarZonificacionUseCase;
use App\Modules\Zonificacion\Application\UseCases\ObtenerZonificacionUseCase;
use App\Modules\Zonificacion\Presentation\Requests\GuardarZonificacionRequest;
use App\Modules\Zonificacion\Presentation\Resources\ZonificacionResource;
use Illuminate\Http\JsonResponse;

class ZonificacionController
{
    public function __construct(
        private readonly ListarZonificacionUseCase $listar,
        private readonly ObtenerZonificacionUseCase $obtener,
        private readonly CrearZonificacionUseCase $crear,
        private readonly ActualizarZonificacionUseCase $actualizar,
        private readonly EliminarZonificacionUseCase $eliminar
    ) {}

    public function index(): JsonResponse
    {
        return new JsonResponse(ZonificacionResource::collection($this->listar->execute()));
    }

    public function show(int $id): JsonResponse
    {
        return new JsonResponse(new ZonificacionResource($this->obtener->execute($id)));
    }

    public function store(GuardarZonificacionRequest $request): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }
        $dto = new GuardarZonificacionDTO(
            codigo: $request->codigo,
            nombre: $request->nombre,
            estaActivo: (bool) $request->esta_activo,
            usuarioId: $user->id
        );
        $entity = $this->crear->execute($dto);

        return new JsonResponse(new ZonificacionResource($entity), 201);
    }

    public function update(GuardarZonificacionRequest $request, int $id): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }
        $dto = new GuardarZonificacionDTO(
            codigo: $request->codigo,
            nombre: $request->nombre,
            estaActivo: (bool) $request->esta_activo,
            usuarioId: $user->id
        );
        $entity = $this->actualizar->execute($id, $dto);

        return new JsonResponse(new ZonificacionResource($entity));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->eliminar->execute($id);

        return new JsonResponse(null, 204);
    }
}
