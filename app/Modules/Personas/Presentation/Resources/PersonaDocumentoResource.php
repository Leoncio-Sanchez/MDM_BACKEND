<?php

declare(strict_types=1);

namespace App\Modules\Personas\Presentation\Resources;

use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PersonaDocumento */
class PersonaDocumentoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_documento' => $this->numeroDocumento,
            'persona' => $this->personaId,
            'tipo_documento_identidad' => $this->tipoDocumentoIdentidadId,
        ];
    }
}
