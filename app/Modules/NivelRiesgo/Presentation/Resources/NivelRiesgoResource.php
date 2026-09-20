<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NivelRiesgoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'codigo' => $this->resource->codigo,
            'nombre' => $this->resource->nombre,
            'esta_activo' => $this->resource->estaActivo,
        ];
    }
}
