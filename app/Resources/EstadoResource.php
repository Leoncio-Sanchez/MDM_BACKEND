<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Estado
 *
 * @property int $id
 * @property string $nombre
 * @property bool $es_para_lf
 * @property bool $es_para_itse
 * @property bool $esta_activo
 */
class EstadoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'es_para_lf' => (bool) ($this->es_para_lf ?? $this->esParaLf ?? false),
            'es_para_itse' => (bool) ($this->es_para_itse ?? $this->esParaItse ?? false),
            'esta_activo' => (bool) ($this->esta_activo ?? $this->estaActivo ?? false),
        ];
    }
}
