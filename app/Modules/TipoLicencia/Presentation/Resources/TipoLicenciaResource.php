<?php

declare(strict_types=1);

namespace App\Modules\TipoLicencia\Presentation\Resources;

use App\Modules\TipoLicencia\Domain\Entities\TipoLicencia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TipoLicenciaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var TipoLicencia $tipo */
        $tipo = $this->resource;

        return [
            'codigo' => $tipo->codigo,
            'esta_activo' => $tipo->estaActivo,
            'id' => $tipo->id,
            'nombre' => $tipo->nombre,
        ];
    }
}
