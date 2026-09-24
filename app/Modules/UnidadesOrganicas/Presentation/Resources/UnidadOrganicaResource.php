<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Presentation\Resources;

use App\Modules\UnidadesOrganicas\Infrastructure\Persistence\Models\UnidadOrganicaModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UnidadOrganicaModel
 */
final class UnidadOrganicaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'sigla' => $this->sigla,
            'esta_activo' => (bool) $this->esta_activo,
        ];
    }
}
