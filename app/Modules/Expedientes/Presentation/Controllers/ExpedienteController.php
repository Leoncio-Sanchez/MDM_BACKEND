<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Expedientes\Application\DTOs\ActualizarExpedienteDTO;
use App\Modules\Expedientes\Application\DTOs\AmpliarPlazoExpedienteDTO;
use App\Modules\Expedientes\Application\DTOs\BuscarAutorizacionImprocedenteDTO;
use App\Modules\Expedientes\Application\DTOs\BuscarExpedientesDTO;
use App\Modules\Expedientes\Application\DTOs\ConsultaExpedientesDTO;
use App\Modules\Expedientes\Application\DTOs\CrearExpedienteDTO;
use App\Modules\Expedientes\Application\DTOs\DenegarItseDTO;
use App\Modules\Expedientes\Application\DTOs\DenegarLicenciaDTO;
use App\Modules\Expedientes\Application\UseCases\ActualizarExpedienteUseCase;
use App\Modules\Expedientes\Application\UseCases\AmpliarPlazoExpedienteUseCase;
use App\Modules\Expedientes\Application\UseCases\AnularExpedienteUseCase;
use App\Modules\Expedientes\Application\UseCases\BuscarAutorizacionImprocedenteUseCase;
use App\Modules\Expedientes\Application\UseCases\BuscarExpedientesUseCase;
use App\Modules\Expedientes\Application\UseCases\ConsultaExpedientesUseCase;
use App\Modules\Expedientes\Application\UseCases\CrearExpedienteUseCase;
use App\Modules\Expedientes\Application\UseCases\DenegarItseUseCase;
use App\Modules\Expedientes\Application\UseCases\DenegarLicenciaUseCase;
use App\Modules\Expedientes\Application\UseCases\ListarExpedientesPendientesUseCase;
use App\Modules\Expedientes\Application\UseCases\ListarExpedientesUseCase;
use App\Modules\Expedientes\Application\UseCases\ObtenerExpedienteUseCase;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\Exceptions\ItseYaEmitidaError;
use App\Modules\Expedientes\Domain\Exceptions\LicenciaYaEmitidaError;
use App\Modules\Expedientes\Domain\Exceptions\TipoAutorizacionInvalidoError;
use App\Modules\Expedientes\Presentation\Requests\ActualizarExpedienteRequest;
use App\Modules\Expedientes\Presentation\Requests\AnularExpedienteRequest;
use App\Modules\Expedientes\Presentation\Requests\CrearExpedienteRequest;
use App\Modules\Expedientes\Presentation\Resources\AutorizacionImprocedenteResource;
use App\Modules\Expedientes\Presentation\Resources\ExpedienteBusquedaResource;
use App\Modules\Expedientes\Presentation\Resources\ExpedienteListResource;
use App\Modules\Expedientes\Presentation\Resources\ExpedientePendienteResource;
use App\Modules\Expedientes\Presentation\Resources\ExpedienteResource;
use App\Shared\Domain\Calendario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

final class ExpedienteController extends Controller
{
    public function __construct(
        private readonly CrearExpedienteUseCase $crear,
        private readonly ActualizarExpedienteUseCase $actualizar,
        private readonly ObtenerExpedienteUseCase $obtener,
        private readonly AnularExpedienteUseCase $anularExpediente,
        private readonly ListarExpedientesUseCase $listar,
        private readonly ListarExpedientesPendientesUseCase $listarPendientes,
        private readonly BuscarExpedientesUseCase $buscar,
        private readonly ConsultaExpedientesUseCase $consulta,
        private readonly BuscarAutorizacionImprocedenteUseCase $buscarAutorizacion,
        private readonly AmpliarPlazoExpedienteUseCase $ampliarPlazo,
        private readonly DenegarLicenciaUseCase $denegarLicencia,
        private readonly DenegarItseUseCase $denegarItse,
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

    /** Reemplaza al antiguo DELETE: el expediente queda como constancia, marcado como anulado */
    public function anular(AnularExpedienteRequest $request, int $pk): ExpedienteResource
    {
        /** @var object{id: int} $user */
        $user = $request->user('api');
        $documento = $request->validated('documento');

        return new ExpedienteResource($this->anularExpediente->execute(
            $pk,
            (string) $request->validated('motivo'),
            is_string($documento) ? $documento : null,
            (int) $user->id,
        ));
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

    public function autorizacionImprocedente(Request $request, int $pk): JsonResponse
    {
        $tipo = (string) $request->query('tipo', '');

        if ($tipo === '') {
            return response()->json(
                ['error' => "El parámetro 'tipo' es requerido ('LF' o 'ITSE')."],
                400,
            );
        }

        try {
            $dto = BuscarAutorizacionImprocedenteDTO::fromParams($pk, $tipo);
            $autorizacion = $this->buscarAutorizacion->execute($dto);
        } catch (TipoAutorizacionInvalidoError $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\ValueError $e) {
            return response()->json(
                ['error' => "Tipo '{$tipo}' no válido. Opciones: LF, ITSE."],
                400,
            );
        }

        if ($autorizacion === null) {
            return new JsonResponse('', 200, [], 0, true);
        }

        return response()->json(
            new AutorizacionImprocedenteResource($autorizacion),
            200,
        );
    }

    public function ampliacionPlazo(Request $request, int $pk): JsonResponse
    {
        try {
            $data = $request->validate([
                'fecha_suspension' => ['required', 'date'],
                'dias_ampliacion' => ['required', 'integer', 'min:1'],
                'motivo_ampliacion' => ['required', 'string', 'max:250'],
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 400);
        }

        /** @var object{id: int} $user */
        $user = $request->user('api');
        $usuarioId = (int) $user->id;

        try {
            $expediente = $this->ampliarPlazo->execute(
                AmpliarPlazoExpedienteDTO::fromArray($pk, $data, $usuarioId),
            );
        } catch (ExpedienteNoEncontrado $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        return response()->json(
            new ExpedienteResource($expediente),
            200,
        );
    }

    public function denegarLicencia(Request $request, int $pk): JsonResponse
    {
        try {
            $data = $request->validate([
                'fecha_rechazo' => ['required', 'date', 'before_or_equal:'.Calendario::hoy()->toDateString()],
                'documento' => ['required', 'string', 'max:100'],
                'observaciones' => ['required', 'string', 'max:1000'],
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 400);
        }

        /** @var object{id: int} $user */
        $user = $request->user('api');
        $usuarioId = (int) $user->id;

        try {
            $autorizacion = $this->denegarLicencia->execute(
                DenegarLicenciaDTO::fromArray($pk, $data, $usuarioId),
            );
        } catch (ExpedienteNoEncontrado $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (LicenciaYaEmitidaError $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(
            new AutorizacionImprocedenteResource($autorizacion),
            201,
        );
    }

    public function denegarItse(Request $request, int $pk): JsonResponse
    {
        try {
            $data = $request->validate([
                'fecha_rechazo' => ['required', 'date', 'before_or_equal:'.Calendario::hoy()->toDateString()],
                'documento' => ['required', 'string', 'max:100'],
                'observaciones' => ['required', 'string', 'max:1000'],
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 400);
        }

        /** @var object{id: int} $user */
        $user = $request->user('api');
        $usuarioId = (int) $user->id;

        try {
            $autorizacion = $this->denegarItse->execute(
                DenegarItseDTO::fromArray($pk, $data, $usuarioId),
            );
        } catch (ExpedienteNoEncontrado $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (ItseYaEmitidaError $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(
            new AutorizacionImprocedenteResource($autorizacion),
            201,
        );
    }
}
