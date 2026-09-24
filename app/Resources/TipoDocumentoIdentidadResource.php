<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\TipoDocumentoIdentidad;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TipoDocumentoIdentidad
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $esta_activo
 * @property bool $es_para_natural
 * @property bool $es_para_juridica
 */
class TipoDocumentoIdentidadResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'codigo' => (string) $this->codigo,
            'nombre' => (string) $this->nombre,
            'esta_activo' => (bool) $this->esta_activo,
            'es_para_natural' => (bool) $this->es_para_natural,
            'es_para_juridica' => (bool) $this->es_para_juridica,
        ];
    }
}
