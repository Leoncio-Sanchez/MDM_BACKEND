<?php

declare(strict_types=1);

namespace App\Modules\Personas\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Personas\Application\DTOs\ActualizarPersonaDTO;
use App\Modules\Personas\Application\DTOs\BuscarPersonasDTO;
use App\Modules\Personas\Application\DTOs\CrearPersonaDTO;
use App\Modules\Personas\Application\UseCases\ActualizarPersonaUseCase;
use App\Modules\Personas\Application\UseCases\BuscarPersonasUseCase;
use App\Modules\Personas\Application\UseCases\CrearPersonaUseCase;
use App\Modules\Personas\Application\UseCases\EliminarPersonaUseCase;
use App\Modules\Personas\Application\UseCases\ListarDocumentosPersonaUseCase;
use App\Modules\Personas\Application\UseCases\ListarPersonasUseCase;
use App\Modules\Personas\Application\UseCases\ListarSexosUseCase;
use App\Modules\Personas\Application\UseCases\ObtenerPersonaUseCase;
use App\Modules\Personas\Presentation\Requests\ActualizarPersonaRequest;
use App\Modules\Personas\Presentation\Requests\BuscarPersonasRequest;
use App\Modules\Personas\Presentation\Requests\CrearPersonaRequest;
use App\Modules\Personas\Presentation\Resources\PersonaDocumentoDetalleResource;
use App\Modules\Personas\Presentation\Resources\PersonaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class PersonaController extends Controller
{
    public function __construct(
        private readonly ListarPersonasUseCase $listar,
        private readonly CrearPersonaUseCase $crear,
        private readonly ObtenerPersonaUseCase $obtener,
        private readonly ActualizarPersonaUseCase $actualizar,
        private readonly EliminarPersonaUseCase $eliminar,
        private readonly BuscarPersonasUseCase $buscar,
        private readonly ListarSexosUseCase $listarSexos,
        private readonly ListarDocumentosPersonaUseCase $listarDocumentos,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(PersonaResource::collection($this->listar->execute()));
    }

    public function store(CrearPersonaRequest $request): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $dto = CrearPersonaDTO::fromArray([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        $persona = $this->crear->execute($dto);

        return (new PersonaResource($persona))->response()->setStatusCode(201);
    }

    public function show(int $id): PersonaResource
    {
        return new PersonaResource($this->obtener->execute($id));
    }

    public function update(ActualizarPersonaRequest $request, int $id): PersonaResource
    {
        $dto = ActualizarPersonaDTO::fromArray($request->validated());

        return new PersonaResource($this->actualizar->execute($id, $dto));
    }

    public function destroy(int $id): Response
    {
        $this->eliminar->execute($id);

        return response()->noContent();
    }

    public function buscar(BuscarPersonasRequest $request): JsonResponse
    {
        $dto = BuscarPersonasDTO::fromArray($request->validated());

        return response()->json($this->buscar->execute($dto));
    }

    public function sexos(): JsonResponse
    {
        return response()->json($this->listarSexos->execute());
    }

    public function documentos(int $id): JsonResponse
    {
        return response()->json(
            PersonaDocumentoDetalleResource::collection($this->listarDocumentos->execute($id))
        );
    }
}
