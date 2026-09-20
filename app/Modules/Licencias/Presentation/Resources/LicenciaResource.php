<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Presentation\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
/** @property \App\Modules\Licencias\Domain\Entities\Licencia $resource */
final class LicenciaResource extends JsonResource {
    /** @return array<string, mixed> */
    public function toArray(Request $request): array {
        return [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'numero_licencia' => $this->resource->numeroLicencia->value,
            'fecha_emision' => $this->resource->fechaEmision->toDateString(),
            'nombre_comercial' => $this->resource->nombreComercial->value,
            'estado' => $this->resource->estado->value,
            'titular_id' => $this->resource->titularId,
            'expediente_id' => $this->resource->expedienteId,
            'tipo_licencia_id' => $this->resource->tipoLicenciaId,
            'nivel_riesgo_id' => $this->resource->nivelRiesgoId,
            'zonificacion_id' => $this->resource->zonificacionId,
    ];
    }
}
