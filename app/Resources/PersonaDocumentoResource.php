<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\PersonaDocumento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PersonaDocumento
 */
class PersonaDocumentoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'numero_documento' => (string) $this->numero_documento,
            'persona' => (int) $this->persona_id,
            'tipo_documento_identidad' => (int) $this->tipo_documento_identidad_id,
        ];
    }
}
