<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\NivelRiesgo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NivelRiesgo
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $esta_activo
 */
class NivelRiesgoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'codigo' => (string) $this->codigo,
            'nombre' => (string) $this->nombre,
            'esta_activo' => (bool) $this->esta_activo,
        ];
    }
}
