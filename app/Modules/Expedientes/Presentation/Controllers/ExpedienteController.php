<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Expedientes\Application\DTOs\ActualizarExpedienteDTO;
use App\Modules\Expedientes\Application\DTOs\BuscarExpedientesDTO;
use App\Modules\Expedientes\Application\DTOs\ConsultaExpedientesDTO;
use App\Modules\Expedientes\Application\DTOs\CrearExpedienteDTO;
use App\Modules\Expedientes\Application\UseCases\ActualizarExpedienteUseCase;
use App\Modules\Expedientes\Application\UseCases\BuscarExpedientesUseCase;
use App\Modules\Expedientes\Application\UseCases\ConsultaExpedientesUseCase;
use App\Modules\Expedientes\Application\UseCases\CrearExpedienteUseCase;
use App\Modules\Expedientes\Application\UseCases\EliminarExpedienteUseCase;
use App\Modules\Expedientes\Application\UseCases\ListarExpedientesPendientesUseCase;
use App\Modules\Expedientes\Application\UseCases\ListarExpedientesUseCase;
use App\Modules\Expedientes\Application\UseCases\ObtenerExpedienteUseCase;
use App\Modules\Expedientes\Presentation\Requests\ActualizarExpedienteRequest;
use App\Modules\Expedientes\Presentation\Requests\CrearExpedienteRequest;
use App\Modules\Expedientes\Presentation\Resources\ExpedienteBusquedaResource;
use App\Modules\Expedientes\Presentation\Resources\ExpedienteListResource;
use App\Modules\Expedientes\Presentation\Resources\ExpedientePendienteResource;
use App\Modules\Expedientes\Presentation\Resources\ExpedienteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ExpedienteController extends Controller
{
    public function __construct(
        private readonly CrearExpedienteUseCase $crear,
        private readonly ActualizarExpedienteUseCase $actualizar,
        private readonly ObtenerExpedienteUseCase $obtener,
        private readonly EliminarExpedienteUseCase $eliminar,
        private readonly ListarExpedientesUseCase $listar,
        private readonly ListarExpedientesPendientesUseCase $listarPendientes,
        private readonly BuscarExpedientesUseCase $buscar,
        private readonly ConsultaExpedientesUseCase $consulta,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $resultado = $this->listar->execute(
            (int) $request->input('page', 1),
            (int) $request->input('per_page', 15)
        );

        return ExpedienteListResource::collection($resultado['items']);
    }

    public function store(CrearExpedienteRequest $request): JsonResponse
    {
        /** @var object{id: int} $user */
        $user = $request->user('api');
        $dto = CrearExpedienteDTO::fromArray($request->validated(), (int) $user->id);

        $expediente = $this->crear->execute($dto);

        return (new ExpedienteResource($expediente))->response()->setStatusCode(201);
    }

    public function show(int $pk): ExpedienteResource
    {
        return new ExpedienteResource($this->obtener->execute($pk));
    }

    public function update(ActualizarExpedienteRequest $request, int $pk): ExpedienteResource
    {
        $dto = ActualizarExpedienteDTO::fromArray($pk, $request->validated());

        return new ExpedienteResource($this->actualizar->execute($dto));
    }

    public function destroy(int $pk): JsonResponse
    {
        $this->eliminar->execute($pk);

        return new JsonResponse(null, 204);
    }

    public function pendientes(): JsonResponse
    {
        $filas = $this->listarPendientes->execute();

        return response()->json(
            ExpedientePendienteResource::collection($filas)->resolve()
        );
    }

    public function buscar(Request $request): JsonResponse
    {
        $filtro = (string) $request->query('filtro', '');
        $valor = (string) $request->query('valor', '');

        if ($filtro === '') {
            return response()->json(
                ['error' => "El parámetro 'filtro' es obligatorio."],
                400
            );
        }

        if ($valor === '') {
            return response()->json(
                ['error' => "El parámetro 'valor' es obligatorio."],
                400
            );
        }

        try {
            $filas = $this->buscar->execute(new BuscarExpedientesDTO($filtro, $valor));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(
            ExpedienteBusquedaResource::collection($filas)->resolve()
        );
    }

    public function consulta(Request $request): JsonResponse
    {
        $numero = $request->query('numero_expediente');
        $dto = new ConsultaExpedientesDTO(
            numeroExpediente: $numero !== null && $numero !== '' ? (int) $numero : null,
        );

        $expedientes = $this->consulta->execute($dto);

        return response()->json(
            ExpedienteResource::collection($expedientes)->resolve()
        );
    }
}
