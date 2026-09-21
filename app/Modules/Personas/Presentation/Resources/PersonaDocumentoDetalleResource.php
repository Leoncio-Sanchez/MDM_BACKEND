<?php

declare(strict_types=1);

namespace App\Modules\Personas\Presentation\Resources;

use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/** @mixin PersonaDocumento */
class PersonaDocumentoDetalleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $tipo = DB::table('tipos_documento_identidad')->where('id', $this->tipoDocumentoIdentidadId)->first();

        return [
            'id' => $this->id,
            'numero_documento' => $this->numeroDocumento,
            'persona_id' => $this->personaId,
            'tipo_documento_identidad_id' => $this->tipoDocumentoIdentidadId,
            'tipos_documento_identidad_codigo' => $tipo ? $tipo->codigo : null,
            'tipos_documento_identidad_nombre' => $tipo ? $tipo->nombre : null,
        ];
    }
}
