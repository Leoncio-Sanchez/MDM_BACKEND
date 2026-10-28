<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Controllers;

use App\Modules\Licencias\Application\DTOs\BuscarLicenciasDTO;
use App\Modules\Licencias\Application\DTOs\ConsultaLicenciasDTO;
use App\Modules\Licencias\Application\DTOs\CrearLicenciaDTO;
use App\Modules\Licencias\Application\DTOs\InactivarLicenciaDTO;
use App\Modules\Licencias\Application\DTOs\ListarEstadosDTO;
use App\Modules\Licencias\Application\DTOs\ModificarLicenciaDTO;
use App\Modules\Licencias\Application\DTOs\RegistrarNotificacionDTO;
use App\Modules\Licencias\Application\DTOs\VerificarExpedienteDTO;
use App\Modules\Licencias\Application\UseCases\BuscarLicenciasUseCase;
use App\Modules\Licencias\Application\UseCases\ConsultaLicenciasUseCase;
use App\Modules\Licencias\Application\UseCases\CrearLicenciaUseCase;
use App\Modules\Licencias\Application\UseCases\EliminarLicenciaUseCase;
use App\Modules\Licencias\Application\UseCases\ListarEstadosLicenciaUseCase;
use App\Modules\Licencias\Application\UseCases\ModificarLicenciaUseCase;
use App\Modules\Licencias\Application\UseCases\RegistrarInactivacionUseCase;
use App\Modules\Licencias\Application\UseCases\RegistrarNotificacionUseCase;
use App\Modules\Licencias\Application\UseCases\VerificarNumeroExpedienteUseCase;
use App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel;
use App\Modules\Licencias\Presentation\Requests\BuscarLicenciasRequest;
use App\Modules\Licencias\Presentation\Requests\ConsultaLicenciasRequest;
use App\Modules\Licencias\Presentation\Requests\CrearLicenciaRequest;
use App\Modules\Licencias\Presentation\Requests\InactivarLicenciaRequest;
use App\Modules\Licencias\Presentation\Requests\ListarEstadosRequest;
use App\Modules\Licencias\Presentation\Requests\ModificarLicenciaRequest;
use App\Modules\Licencias\Presentation\Requests\RegistrarNotificacionRequest;
use App\Modules\Licencias\Presentation\Requests\VerificarExpedienteRequest;
use App\Modules\Licencias\Presentation\Resources\LicenciaListResource;
use App\Modules\Licencias\Presentation\Resources\LicenciaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class LicenciaController
{
    public function __construct(
        private readonly CrearLicenciaUseCase $crear,
        private readonly ModificarLicenciaUseCase $modificar,
        private readonly EliminarLicenciaUseCase $eliminar,
        private readonly BuscarLicenciasUseCase $buscar,
        private readonly ConsultaLicenciasUseCase $consulta,
        private readonly VerificarNumeroExpedienteUseCase $verificar,
        private readonly RegistrarInactivacionUseCase $inactivar,
        private readonly RegistrarNotificacionUseCase $notificar,
        private readonly ListarEstadosLicenciaUseCase $listarEstados
    ) {}

    public function store(CrearLicenciaRequest $request): JsonResponse
    {
        $licencia = $this->crear->execute(CrearLicenciaDTO::fromArray([
            ...$request->validated(),
            'usuario_id' => $request->user('api')?->getAuthIdentifier(),
        ]));

        return (new LicenciaResource($licencia))->response()->setStatusCode(201);
    }

    public function buscar(BuscarLicenciasRequest $request): AnonymousResourceCollection
    {
        $resultados = $this->buscar->execute(BuscarLicenciasDTO::fromArray($request->validated()));

        return LicenciaListResource::collection($resultados);
    }

    public function update(ModificarLicenciaRequest $request, int $pk): LicenciaResource
    {
        $licencia = $this->modificar->execute(ModificarLicenciaDTO::fromArray(array_merge($request->validated(), [
            'licencia_id' => $pk,
            'usuario_id' => $request->user('api')?->getAuthIdentifier(),
        ])));

        return new LicenciaResource($licencia);
    }

    public function verificarExpediente(VerificarExpedienteRequest $request): JsonResponse
    {
        return new JsonResponse(
            $this->verificar->execute(VerificarExpedienteDTO::fromArray($request->validated()))
        );
    }

    public function inactivar(InactivarLicenciaRequest $request): LicenciaResource
    {
        $licencia = $this->inactivar->execute(InactivarLicenciaDTO::fromArray([
            ...$request->validated(),
            'usuario_id' => $request->user('api')?->getAuthIdentifier(),
        ]));

        return new LicenciaResource($licencia);
    }

    public function destroy(int $pk): JsonResponse
    {
        $this->eliminar->execute($pk);

        return new JsonResponse(null, 204);
    }

    public function consulta(ConsultaLicenciasRequest $request): AnonymousResourceCollection
    {
        $resultados = $this->consulta->execute(ConsultaLicenciasDTO::fromArray($request->validated()));

        return LicenciaResource::collection($resultados);
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

    public function giros(int $pk): JsonResponse
    {
        $licencia = LicenciaModel::find($pk);
        if (! $licencia) {
            return new JsonResponse(['error' => 'La licencia de funcionamiento no existe.'], 404);
        }

        $giros = DB::table('licencias_funcionamiento_giros as lfg')
            ->join('giros as g', 'g.id', '=', 'lfg.giro_id')
            ->where('lfg.licencia_funcionamiento_id', $pk)
            ->select([
                'lfg.id',
                'lfg.licencia_funcionamiento_id',
                'lfg.giro_id',
                'g.ciiu_id',
                'g.nombre',
            ])
            ->get();

        return new JsonResponse($giros, 200);
    }
}
