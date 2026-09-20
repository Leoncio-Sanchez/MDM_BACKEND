<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Licencias\Domain\Entities\Licencia;

class LicenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Licencia) {
            return [
                'id' => $this->resource->id,
                'uuid' => $this->resource->uuid,
                'numero_licencia' => $this->resource->numeroLicencia->value,
                'nombre_comercial' => $this->resource->nombreComercial->value,
                'fecha_emision' => $this->resource->fechaEmision->toDateString(),
                'titular' => $this->resource->titularId,
                'conductor' => $this->resource->conductorId,
                'licencia_principal' => $this->resource->licenciaPrincipalId,
                'expediente' => $this->resource->expedienteId,
                'tipo_licencia' => $this->resource->tipoLicenciaId,
                'nivel_riesgo' => $this->resource->nivelRiesgoId,
                'zonificacion' => $this->resource->zonificacionId,
                'es_vigencia_indeterminada' => $this->resource->esVigenciaIndeterminada,
                'fecha_inicio_vigencia' => $this->resource->fechaInicioVigencia?->toDateString(),
                'fecha_fin_vigencia' => $this->resource->fechaFinVigencia?->toDateString(),
                'actividad' => $this->resource->actividad,
                'direccion' => $this->resource->direccion,
                'hora_desde' => $this->resource->horaDesde,
                'hora_hasta' => $this->resource->horaHasta,
                'resolucion_numero' => $this->resource->resolucionNumero,
                'area' => $this->resource->area,
                'numero_recibo_pago' => $this->resource->numeroReciboPago,
                'observaciones' => $this->resource->observaciones,
                'se_puede_publicar' => $this->resource->sePuedePublicar,
                'fecha_notificacion' => $this->resource->fechaNotificacion?->toDateTimeString(),
                'estado' => $this->resource->estado?->value,
                'giros' => [],
                'archivos' => [],
                'historial_estados' => [],
            ];
        }

        $arr = (array) $this->resource;
        return [
            'id' => $arr['id'] ?? null,
            'uuid' => $arr['uuid'] ?? null,
            'numero_licencia' => $arr['numero_licencia'] ?? null,
            'nombre_comercial' => $arr['nombre_comercial'] ?? null,
            'fecha_emision' => $arr['fecha_emision'] ?? null,
            'titular' => $arr['titular_id'] ?? $arr['titular'] ?? null,
            'conductor' => $arr['conductor_id'] ?? $arr['conductor'] ?? null,
            'licencia_principal' => $arr['licencia_principal_id'] ?? $arr['licencia_principal'] ?? null,
            'expediente' => $arr['expediente_id'] ?? $arr['expediente'] ?? null,
            'tipo_licencia' => $arr['tipo_licencia_id'] ?? $arr['tipo_licencia'] ?? null,
            'nivel_riesgo' => $arr['nivel_riesgo_id'] ?? $arr['nivel_riesgo'] ?? null,
            'zonificacion' => $arr['zonificacion_id'] ?? $arr['zonificacion'] ?? null,
            'es_vigencia_indeterminada' => (bool)($arr['es_vigencia_indeterminada'] ?? false),
            'fecha_inicio_vigencia' => $arr['fecha_inicio_vigencia'] ?? null,
            'fecha_fin_vigencia' => $arr['fecha_fin_vigencia'] ?? null,
            'actividad' => $arr['actividad'] ?? null,
            'direccion' => $arr['direccion'] ?? null,
            'hora_desde' => $arr['hora_desde'] ?? null,
            'hora_hasta' => $arr['hora_hasta'] ?? null,
            'resolucion_numero' => $arr['resolucion_numero'] ?? null,
            'area' => (float)($arr['area'] ?? 0),
            'numero_recibo_pago' => $arr['numero_recibo_pago'] ?? null,
            'observaciones' => $arr['observaciones'] ?? null,
            'se_puede_publicar' => (bool)($arr['se_puede_publicar'] ?? false),
            'fecha_notificacion' => $arr['fecha_notificacion'] ?? null,
            'estado' => $arr['estado'] ?? null,
        ];
    }
}
