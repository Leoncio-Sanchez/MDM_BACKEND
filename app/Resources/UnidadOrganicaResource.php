<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\UnidadOrganica;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UnidadOrganica
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $sigla
 * @property bool $esta_activo
 */
class UnidadOrganicaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'codigo' => (string) $this->codigo,
            'nombre' => (string) $this->nombre,
            'sigla' => (string) $this->sigla,
            'esta_activo' => (bool) $this->esta_activo,
        ];
    }
}
