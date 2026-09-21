<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ITSE\Application\DTOs\{
    ActualizarItseDTO,
    BuscarItseDTO,
    CrearItseDTO,
    InactivarItseDTO,
    NotificarItseDTO,
    VerificarExpedienteItseDTO
};
use App\Modules\ITSE\Application\UseCases\{
    ActualizarItseUseCase,
    BuscarItseUseCase,
    CrearItseUseCase,
    InactivarItseUseCase,
    NotificarItseUseCase,
    ObtenerItseUseCase,
    VerificarExpedienteItseUseCase
};
use App\Modules\ITSE\Presentation\Requests\{
    ActualizarItseRequest,
    BuscarItseRequest,
    CrearItseRequest,
    InactivarItseRequest,
    NotificarItseRequest,
    VerificarExpedienteRequest
};
use App\Modules\ITSE\Presentation\Resources\ItseResource;
use Illuminate\Http\JsonResponse;

final class ItseController extends Controller
{
    public function __construct(
        private readonly CrearItseUseCase $crear,
        private readonly ActualizarItseUseCase $actualizar,
        private readonly ObtenerItseUseCase $obtener,
        private readonly BuscarItseUseCase $buscar,
        private readonly VerificarExpedienteItseUseCase $verificar,
        private readonly NotificarItseUseCase $notificar,
        private readonly InactivarItseUseCase $inactivar,
    ) {}

    public function store(CrearItseRequest $request): JsonResponse
    {
        $user = $request->user('api');
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validated();
        $data['usuario_id'] = $user->id;
        $dto = CrearItseDTO::fromArray($data);

        $itse = $this->crear->execute($dto);
        return (new ItseResource($itse))->response()->setStatusCode(201);
    }

    public function show(int $pk): ItseResource
    {
        return new ItseResource($this->obtener->execute($pk));
    }

    public function update(ActualizarItseRequest $request, int $pk): ItseResource
    {
        $data = $request->validated();
        $data['id'] = $pk;
        $dto = ActualizarItseDTO::fromArray($data);
        return new ItseResource($this->actualizar->execute($dto));
    }

    public function buscar(BuscarItseRequest $request): JsonResponse
    {
        return response()->json(
            $this->buscar->execute(BuscarItseDTO::fromArray($request->validated())),
            200,
            [],
            JSON_PRESERVE_ZERO_FRACTION
        );
    }

    public function verificarExpediente(VerificarExpedienteRequest $request): JsonResponse
    {
        return response()->json(
            $this->verificar->execute(VerificarExpedienteItseDTO::fromArray($request->validated()))
        );
    }

    public function notificacion(NotificarItseRequest $request, int $pk): ItseResource
    {
        $data = $request->validated();
        $data['itse_id'] = $pk;
        $dto = NotificarItseDTO::fromArray($data);
        return new ItseResource($this->notificar->execute($dto));
    }

    public function inactivar(InactivarItseRequest $request): ItseResource|JsonResponse
    {
        $user = $request->user('api');
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validated();
        $data['usuario_id'] = $user->id;
        $dto = InactivarItseDTO::fromArray($data);

        return new ItseResource($this->inactivar->execute($dto));
    }
}
