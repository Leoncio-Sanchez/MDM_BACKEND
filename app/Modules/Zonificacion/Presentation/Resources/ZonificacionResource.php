<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Presentation\Resources;

use App\Modules\Zonificacion\Domain\Entities\Zonificacion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Zonificacion $resource
 */
class ZonificacionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'codigo' => $this->resource->codigo,
            'nombre' => $this->resource->nombre,
            'esta_activo' => $this->resource->estaActivo,
            'fecha_digitacion' => $this->resource->fechaDigitacion->setTimezone(new \DateTimeZone('America/Lima'))->format('Y-m-d\TH:i:s.uP'),
            'usuario' => $this->resource->usuarioId,
        ];
    }
}
