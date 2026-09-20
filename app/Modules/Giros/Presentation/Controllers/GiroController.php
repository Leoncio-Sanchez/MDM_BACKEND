<?php

declare(strict_types=1);

namespace App\Modules\Giros\Presentation\Controllers;

use App\Modules\Giros\Application\DTOs\ActualizarGiroDTO;
use App\Modules\Giros\Application\DTOs\CrearGiroDTO;
use App\Modules\Giros\Application\UseCases\ActualizarGiroUseCase;
use App\Modules\Giros\Application\UseCases\CrearGiroUseCase;
use App\Modules\Giros\Application\UseCases\EliminarGiroUseCase;
use App\Modules\Giros\Application\UseCases\ListarGirosUseCase;
use App\Modules\Giros\Application\UseCases\ObtenerGiroUseCase;
use App\Modules\Giros\Presentation\Requests\ActualizarGiroRequest;
use App\Modules\Giros\Presentation\Requests\CrearGiroRequest;
use App\Modules\Giros\Presentation\Resources\GiroResource;
use Illuminate\Http\JsonResponse;

class GiroController
{
    public function __construct(
        private readonly ListarGirosUseCase $listar,
        private readonly ObtenerGiroUseCase $obtener,
        private readonly CrearGiroUseCase $crear,
        private readonly ActualizarGiroUseCase $actualizar,
        private readonly EliminarGiroUseCase $eliminar
    ) {}

    public function index(): JsonResponse
    {
        return new JsonResponse(GiroResource::collection($this->listar->execute()));
    }

    public function show(int $id): JsonResponse
    {
        return new JsonResponse(new GiroResource($this->obtener->execute($id)));
    }

    public function store(CrearGiroRequest $request): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new CrearGiroDTO(
            ciiuId: $request->ciiu_id !== null ? (int) $request->ciiu_id : null,
            nombre: $request->nombre,
            estaActivo: (bool) $request->esta_activo,
            usuarioId: $user->id
        );
        $entity = $this->crear->execute($dto);

        return new JsonResponse(new GiroResource($entity), 201);
    }

    public function update(ActualizarGiroRequest $request, int $id): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new ActualizarGiroDTO(
            ciiuId: $request->ciiu_id !== null ? (int) $request->ciiu_id : null,
            nombre: $request->nombre,
            estaActivo: $request->has('esta_activo') ? (bool) $request->esta_activo : null,
            usuarioId: $user->id
        );
        $entity = $this->actualizar->execute($id, $dto);

        return new JsonResponse(new GiroResource($entity));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->eliminar->execute($id);

        return new JsonResponse(null, 204);
    }
}
