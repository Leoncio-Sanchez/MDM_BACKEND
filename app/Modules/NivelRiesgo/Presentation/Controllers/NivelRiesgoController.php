<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Presentation\Controllers;

use App\Modules\NivelRiesgo\Application\DTOs\GuardarNivelRiesgoDTO;
use App\Modules\NivelRiesgo\Application\UseCases\ActualizarNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Application\UseCases\CrearNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Application\UseCases\EliminarNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Application\UseCases\ListarNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Application\UseCases\ObtenerNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Presentation\Requests\GuardarNivelRiesgoRequest;
use App\Modules\NivelRiesgo\Presentation\Resources\NivelRiesgoResource;
use Illuminate\Http\JsonResponse;

class NivelRiesgoController
{
    public function __construct(
        private readonly ListarNivelRiesgoUseCase $listar,
        private readonly ObtenerNivelRiesgoUseCase $obtener,
        private readonly CrearNivelRiesgoUseCase $crear,
        private readonly ActualizarNivelRiesgoUseCase $actualizar,
        private readonly EliminarNivelRiesgoUseCase $eliminar
    ) {}

    public function index(): JsonResponse
    {
        return new JsonResponse(NivelRiesgoResource::collection($this->listar->execute()));
    }

    public function show(int $id): JsonResponse
    {
        return new JsonResponse(new NivelRiesgoResource($this->obtener->execute($id)));
    }

    public function store(GuardarNivelRiesgoRequest $request): JsonResponse
    {
        $dto = new GuardarNivelRiesgoDTO($request->codigo, $request->nombre, (bool) $request->esta_activo);
        $entity = $this->crear->execute($dto);

        return new JsonResponse(new NivelRiesgoResource($entity), 201);
    }

    public function update(GuardarNivelRiesgoRequest $request, int $id): JsonResponse
    {
        $dto = new GuardarNivelRiesgoDTO($request->codigo, $request->nombre, (bool) $request->esta_activo);
        $entity = $this->actualizar->execute($id, $dto);

        return new JsonResponse(new NivelRiesgoResource($entity));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->eliminar->execute($id);

        return new JsonResponse(null, 204);
    }
}
