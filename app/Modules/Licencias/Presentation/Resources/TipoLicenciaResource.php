<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TipoLicenciaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->codigo,
            'esta_activo' => (bool) $this->esta_activo,
            'id' => $this->id,
            'nombre' => $this->nombre,
        ];
    }
}
