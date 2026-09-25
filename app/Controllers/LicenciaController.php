<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LicenciaFuncionamiento;
use App\Requests\Licencias\BuscarLicenciaRequest;
use App\Requests\Licencias\ConsultaLicenciaRequest;
use App\Requests\Licencias\InactivarLicenciaRequest;
use App\Requests\Licencias\NotificarLicenciaRequest;
use App\Requests\Licencias\ReporteLicenciaRequest;
use App\Requests\Licencias\StoreLicenciaRequest;
use App\Requests\Licencias\UpdateLicenciaRequest;
use App\Requests\Licencias\VerificarExpedienteRequest;
use App\Resources\LicenciaListResource;
use App\Resources\LicenciaReporteResource;
use App\Resources\LicenciaResource;
use App\Services\LicenciaService;
use Illuminate\Http\JsonResponse;

final class LicenciaController extends Controller
{
    public function __construct(
        private readonly LicenciaService $licenciaService,
    ) {}

    public function store(StoreLicenciaRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $licencia = $this->licenciaService->crear(
            $request->validated(),
            $userId !== null ? (int) $userId : null
        );

        return response()->json(
            (new LicenciaResource($licencia))->resolve(),
            201
        );
    }

    public function buscar(BuscarLicenciaRequest $request): JsonResponse
    {
        $filtro = (string) $request->input('filtro');
        $valor = (string) $request->input('valor');
        $resultados = $this->licenciaService->buscar($filtro, $valor);

        return response()->json(
            LicenciaListResource::collection($resultados)->resolve()
        );
    }

    public function consulta(ConsultaLicenciaRequest $request): JsonResponse
    {
        $resultados = $this->licenciaService->consulta($request->validated());

        return response()->json(
            LicenciaResource::collection($resultados)->resolve()
        );
    }

    public function verificarExpediente(VerificarExpedienteRequest $request): JsonResponse
    {
        $numero = (int) $request->input('numero_expediente');
        $anio = (int) $request->input('anio');
        $resultado = $this->licenciaService->verificarExpediente($numero, $anio);

        return response()->json($resultado, 200);
    }

    public function inactivar(InactivarLicenciaRequest $request): JsonResponse
    {
        $licenciaId = (int) ($request->input('licencia_funcionamiento_id') ?? $request->input('licencia_id'));
        $userId = $request->user()?->id;
        $resultado = $this->licenciaService->inactivar(
            $licenciaId,
            $request->validated(),
            $userId !== null ? (int) $userId : null
        );

        return response()->json([
            'id' => $resultado['id'],
            'mensaje' => 'Inactivación de la licencia registrada correctamente.',
        ], 201);
    }

    public function show(int $pk): JsonResponse
    {
        $licencia = LicenciaFuncionamiento::find($pk);
        if (! $licencia) {
            return response()->json(['error' => 'La licencia de funcionamiento no existe.'], 404);
        }

        return response()->json(
            (new LicenciaResource($licencia))->resolve(),
            200
        );
    }

    public function update(UpdateLicenciaRequest $request, int $pk): JsonResponse
    {
        $licencia = $this->licenciaService->actualizar($pk, $request->validated());

        return response()->json(
            (new LicenciaResource($licencia))->resolve(),
            200
        );
    }

    public function destroy(int $pk): JsonResponse
    {
        $this->licenciaService->eliminar($pk);

        return response()->json(null, 204);
    }

    public function notificar(NotificarLicenciaRequest $request, int $pk): JsonResponse
    {
        $fecha = (string) ($request->input('fecha_notificacion') ?? $request->input('fecha'));
        $this->licenciaService->notificar($pk, $fecha);

        return response()->json(
            ['mensaje' => 'Fecha de notificación registrada correctamente.'],
            200
        );
    }

    public function listarEstados(int $pk): JsonResponse
    {
        $estados = $this->licenciaService->listarEstados($pk);

        return response()->json($estados, 200);
    }

    public function giros(int $pk): JsonResponse
    {
        $giros = $this->licenciaService->giros($pk);

        return response()->json($giros, 200);
    }

    public function reporte(ReporteLicenciaRequest $request): JsonResponse
    {
        $datos = $this->licenciaService->generarReporte($request->validated());

        return response()->json(
            LicenciaReporteResource::collection($datos)->resolve(),
            200
        );
    }
}
