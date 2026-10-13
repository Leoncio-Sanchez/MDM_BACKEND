<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Presentation\Resources;

use App\Modules\UnidadesOrganicas\Domain\Entities\UnidadOrganica;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property UnidadOrganica $resource
 */
final class UnidadOrganicaResource extends JsonResource
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
            'sigla' => $this->resource->sigla,
            'esta_activo' => $this->resource->estaActivo,
        ];
    }
}
