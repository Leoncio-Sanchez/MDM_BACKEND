<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Licencias\Domain\Entities\Licencia;

class LicenciaListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Licencia) {
            return [
                'id' => $this->resource->id,
                'numero_licencia' => $this->resource->numeroLicencia->value,
                'nombre_comercial' => $this->resource->nombreComercial->value,
                'fecha_emision' => $this->resource->fechaEmision->toDateString(),
                'estado' => $this->resource->estado?->value,
                'expediente' => $this->resource->expedienteId,
                'titular' => $this->resource->titularId,
                'uuid' => $this->resource->uuid,
            ];
        }

        $arr = (array) $this->resource;
        return [
            'id' => $arr['id'] ?? null,
            'numero_licencia' => $arr['numero_licencia'] ?? null,
            'nombre_comercial' => $arr['nombre_comercial'] ?? null,
            'fecha_emision' => $arr['fecha_emision'] ?? null,
            'estado' => $arr['estado'] ?? null,
            'expediente' => $arr['expediente'] ?? $arr['expediente_id'] ?? null,
            'titular' => $arr['titular'] ?? $arr['titular_id'] ?? null,
            'uuid' => $arr['uuid'] ?? null,
        ];
    }
}
