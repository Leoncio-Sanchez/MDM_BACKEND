<?php

declare(strict_types=1);

namespace App\Modules\Estados\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $nombre
 * @property bool $esParaLf
 * @property bool $esParaItse
 * @property bool $estaActivo
 */
final class EstadoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'es_para_lf' => $this->esParaLf,
            'es_para_itse' => $this->esParaItse,
            'esta_activo' => $this->estaActivo,
        ];
    }
}
