<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\TipoLicencia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TipoLicencia
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $esta_activo
 */
class TipoLicenciaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => (string) $this->codigo,
            'esta_activo' => (bool) $this->esta_activo,
            'id' => (int) $this->id,
            'nombre' => (string) $this->nombre,
        ];
    }
}
