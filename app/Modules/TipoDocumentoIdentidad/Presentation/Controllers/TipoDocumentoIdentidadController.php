<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Presentation\Controllers;

use App\Modules\TipoDocumentoIdentidad\Application\DTOs\GuardarTipoDocumentoIdentidadDTO;
use App\Modules\TipoDocumentoIdentidad\Application\UseCases\ActualizarTipoDocumentoIdentidadUseCase;
use App\Modules\TipoDocumentoIdentidad\Application\UseCases\CrearTipoDocumentoIdentidadUseCase;
use App\Modules\TipoDocumentoIdentidad\Application\UseCases\EliminarTipoDocumentoIdentidadUseCase;
use App\Modules\TipoDocumentoIdentidad\Application\UseCases\ListarTiposDocumentoIdentidadUseCase;
use App\Modules\TipoDocumentoIdentidad\Application\UseCases\ObtenerTipoDocumentoIdentidadUseCase;
use App\Modules\TipoDocumentoIdentidad\Presentation\Requests\GuardarTipoDocumentoIdentidadRequest;
use App\Modules\TipoDocumentoIdentidad\Presentation\Resources\TipoDocumentoIdentidadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoDocumentoIdentidadController
{
    public function __construct(
        private readonly ListarTiposDocumentoIdentidadUseCase $listar,
        private readonly ObtenerTipoDocumentoIdentidadUseCase $obtener,
        private readonly CrearTipoDocumentoIdentidadUseCase $crear,
        private readonly ActualizarTipoDocumentoIdentidadUseCase $actualizar,
        private readonly EliminarTipoDocumentoIdentidadUseCase $eliminar
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tipoPersona = $request->query('tipo_persona');
        if ($tipoPersona !== null && $tipoPersona !== '' && ! in_array($tipoPersona, ['N', 'J'], true)) {
            return new JsonResponse(['error' => "tipo_persona '{$tipoPersona}' no válido. Opciones: N (natural), J (jurídica)."], 400);
        }

        $items = $this->listar->execute($tipoPersona ?: null);

        return new JsonResponse(TipoDocumentoIdentidadResource::collection($items));
    }

    public function show(int $id): JsonResponse
    {
        return new JsonResponse(new TipoDocumentoIdentidadResource($this->obtener->execute($id)));
    }

    public function store(GuardarTipoDocumentoIdentidadRequest $request): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new GuardarTipoDocumentoIdentidadDTO(
            codigo: $request->codigo,
            nombre: $request->nombre,
            estaActivo: (bool) $request->esta_activo,
            esParaNatural: (bool) $request->es_para_natural,
            esParaJuridica: (bool) $request->es_para_juridica
        );
        $entity = $this->crear->execute($dto);

        return new JsonResponse(new TipoDocumentoIdentidadResource($entity), 201);
    }

    public function update(GuardarTipoDocumentoIdentidadRequest $request, int $id): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new GuardarTipoDocumentoIdentidadDTO(
            codigo: $request->codigo,
            nombre: $request->nombre,
            estaActivo: (bool) $request->esta_activo,
            esParaNatural: (bool) $request->es_para_natural,
            esParaJuridica: (bool) $request->es_para_juridica
        );
        $entity = $this->actualizar->execute($id, $dto);

        return new JsonResponse(new TipoDocumentoIdentidadResource($entity));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->eliminar->execute($id);

        return new JsonResponse(null, 204);
    }
}
