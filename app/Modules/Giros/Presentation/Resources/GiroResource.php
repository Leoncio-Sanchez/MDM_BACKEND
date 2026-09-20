<?php

declare(strict_types=1);

namespace App\Modules\Giros\Presentation\Resources;

use App\Modules\Giros\Domain\Entities\Giro;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Giro $resource
 */
class GiroResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'ciiu_id' => $this->resource->ciiuId,
            'nombre' => $this->resource->nombre,
            'esta_activo' => $this->resource->estaActivo,
            'fecha_digitacion' => $this->resource->fechaDigitacion?->setTimezone(new \DateTimeZone('America/Lima'))->format('Y-m-d\TH:i:s.uP'),
            'usuario' => $this->resource->usuarioId,
        ];
    }
}
