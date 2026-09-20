<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Licencias\Application\DTOs\{
    BuscarLicenciasDTO, ConsultaLicenciasDTO, CrearLicenciaDTO, InactivarLicenciaDTO,
    ModificarLicenciaDTO, RegistrarNotificacionDTO, ReporteLicenciasDTO,
    VerificarExpedienteDTO, ListarEstadosDTO
};
use App\Modules\Licencias\Application\UseCases\{
    BuscarLicenciasUseCase, ConsultaLicenciasUseCase, CrearLicenciaUseCase, EliminarLicenciaUseCase,
    ListarEstadosLicenciaUseCase, ModificarLicenciaUseCase,
    RegistrarInactivacionUseCase, RegistrarNotificacionUseCase,
    ReporteLicenciasUseCase, VerificarNumeroExpedienteUseCase
};
use App\Modules\Licencias\Presentation\Requests\{
    BuscarLicenciasRequest, ConsultaLicenciasRequest, CrearLicenciaRequest, InactivarLicenciaRequest,
    ModificarLicenciaRequest, RegistrarNotificacionRequest,
    ReporteLicenciasRequest, VerificarExpedienteRequest, ListarEstadosRequest
};
use App\Modules\Licencias\Presentation\Resources\{
    LicenciaListResource, LicenciaReporteResource, LicenciaResource
};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class LicenciaController extends Controller
{
    public function __construct(
        private readonly CrearLicenciaUseCase $crear,
        private readonly BuscarLicenciasUseCase $buscar,
        private readonly ConsultaLicenciasUseCase $consulta,
        private readonly ModificarLicenciaUseCase $modificar,
        private readonly EliminarLicenciaUseCase $eliminar,
        private readonly ReporteLicenciasUseCase $reporte,
        private readonly VerificarNumeroExpedienteUseCase $verificar,
        private readonly RegistrarInactivacionUseCase $inactivar,
        private readonly RegistrarNotificacionUseCase $notificar,
        private readonly ListarEstadosLicenciaUseCase $listarEstados,
    ) {}

    public function store(CrearLicenciaRequest $request): JsonResponse
    {
        $licencia = $this->crear->execute(CrearLicenciaDTO::fromArray($request->validated()));
        return (new LicenciaResource($licencia))->response()->setStatusCode(201);
    }

    public function buscar(BuscarLicenciasRequest $request): AnonymousResourceCollection
    {
        $resultados = $this->buscar->execute(BuscarLicenciasDTO::fromArray($request->validated()));
        return LicenciaListResource::collection($resultados);
    }

    public function update(ModificarLicenciaRequest $request, int $pk): LicenciaResource
    {
        $licencia = $this->modificar->execute(ModificarLicenciaDTO::fromArray(array_merge($request->validated(), ['licencia_id' => $pk])));
        return new LicenciaResource($licencia);
    }

    public function reporte(ReporteLicenciasRequest $request): AnonymousResourceCollection
    {
        $datos = $this->reporte->execute(ReporteLicenciasDTO::fromArray($request->validated()));
        return LicenciaReporteResource::collection($datos);
    }

    public function verificarExpediente(VerificarExpedienteRequest $request): JsonResponse
    {
        return new JsonResponse(
            $this->verificar->execute(VerificarExpedienteDTO::fromArray($request->validated()))
        );
    }

    public function inactivar(InactivarLicenciaRequest $request): LicenciaResource
    {
        $licencia = $this->inactivar->execute(InactivarLicenciaDTO::fromArray($request->validated()));
        return new LicenciaResource($licencia);
    }

    public function destroy(int $pk): JsonResponse
    {
        $this->eliminar->execute($pk);
        return new JsonResponse(null, 204);
    }

    public function consulta(ConsultaLicenciasRequest $request): AnonymousResourceCollection {
        $resultados = $this->consulta->execute(ConsultaLicenciasDTO::fromArray($request->validated()));
        return LicenciaListResource::collection($resultados);
    }

    public function notificar(RegistrarNotificacionRequest $request): JsonResponse
    {
        $this->notificar->execute(RegistrarNotificacionDTO::fromArray($request->validated()));
        return new JsonResponse([], 200);
    }

    public function listarEstados(ListarEstadosRequest $request): JsonResponse
    {
        $estados = $this->listarEstados->execute(ListarEstadosDTO::fromArray($request->validated()));
        return new JsonResponse($estados, 200);
    }
}
