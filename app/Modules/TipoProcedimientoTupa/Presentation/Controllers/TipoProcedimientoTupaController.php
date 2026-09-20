<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Presentation\Controllers;

use App\Modules\TipoProcedimientoTupa\Application\DTOs\ActualizarTipoProcedimientoTupaDTO;
use App\Modules\TipoProcedimientoTupa\Application\DTOs\CrearTipoProcedimientoTupaDTO;
use App\Modules\TipoProcedimientoTupa\Application\UseCases\ActualizarTipoProcedimientoTupaUseCase;
use App\Modules\TipoProcedimientoTupa\Application\UseCases\CrearTipoProcedimientoTupaUseCase;
use App\Modules\TipoProcedimientoTupa\Application\UseCases\EliminarTipoProcedimientoTupaUseCase;
use App\Modules\TipoProcedimientoTupa\Application\UseCases\ListarTiposProcedimientoTupaUseCase;
use App\Modules\TipoProcedimientoTupa\Application\UseCases\ObtenerTipoProcedimientoTupaUseCase;
use App\Modules\TipoProcedimientoTupa\Presentation\Requests\ActualizarTipoProcedimientoTupaRequest;
use App\Modules\TipoProcedimientoTupa\Presentation\Requests\CrearTipoProcedimientoTupaRequest;
use App\Modules\TipoProcedimientoTupa\Presentation\Resources\TipoProcedimientoTupaResource;
use Illuminate\Http\JsonResponse;

class TipoProcedimientoTupaController
{
    public function __construct(
        private readonly ListarTiposProcedimientoTupaUseCase $listar,
        private readonly ObtenerTipoProcedimientoTupaUseCase $obtener,
        private readonly CrearTipoProcedimientoTupaUseCase $crear,
        private readonly ActualizarTipoProcedimientoTupaUseCase $actualizar,
        private readonly EliminarTipoProcedimientoTupaUseCase $eliminar
    ) {}

    public function index(): JsonResponse
    {
        return new JsonResponse(TipoProcedimientoTupaResource::collection($this->listar->execute()));
    }

    public function show(int $id): JsonResponse
    {
        return new JsonResponse(new TipoProcedimientoTupaResource($this->obtener->execute($id)));
    }

    public function store(CrearTipoProcedimientoTupaRequest $request): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new CrearTipoProcedimientoTupaDTO(
            codigo: $request->codigo,
            nombre: $request->nombre,
            plazoAtencionDias: (int) $request->plazo_atencion_dias,
            diasAlertaVencimiento: (int) $request->dias_alerta_vencimiento,
            requiereLf: $request->has('requiere_lf') ? (bool) $request->requiere_lf : false,
            requiereItse: $request->has('requiere_itse') ? (bool) $request->requiere_itse : false,
            unidadOrganicaId: (int) $request->unidad_organica_id,
            usuarioId: $user->id
        );
        $entity = $this->crear->execute($dto);

        return new JsonResponse(new TipoProcedimientoTupaResource($entity), 201);
    }

    public function update(ActualizarTipoProcedimientoTupaRequest $request, int $id): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new ActualizarTipoProcedimientoTupaDTO(
            codigo: $request->codigo,
            nombre: $request->nombre,
            plazoAtencionDias: $request->has('plazo_atencion_dias') ? (int) $request->plazo_atencion_dias : null,
            diasAlertaVencimiento: $request->has('dias_alerta_vencimiento') ? (int) $request->dias_alerta_vencimiento : null,
            estaActivo: $request->has('esta_activo') ? (bool) $request->esta_activo : null,
            requiereLf: $request->has('requiere_lf') ? (bool) $request->requiere_lf : null,
            requiereItse: $request->has('requiere_itse') ? (bool) $request->requiere_itse : null,
            unidadOrganicaId: $request->has('unidad_organica_id') ? (int) $request->unidad_organica_id : null
        );
        $entity = $this->actualizar->execute($id, $dto);

        return new JsonResponse(new TipoProcedimientoTupaResource($entity));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->eliminar->execute($id);

        return new JsonResponse(null, 204);
    }
}
