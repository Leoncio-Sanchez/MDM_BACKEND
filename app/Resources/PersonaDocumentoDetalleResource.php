<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\PersonaDocumento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/**
 * @mixin PersonaDocumento
 */
class PersonaDocumentoDetalleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tipoId = (int) $this->tipo_documento_identidad_id;
        $tipo = $this->tipoDocumentoIdentidad ?? DB::table('tipos_documento_identidad')->where('id', $tipoId)->first();

        return [
            'id' => (int) $this->id,
            'persona_id' => (int) $this->persona_id,
            'tipo_documento_identidad_id' => $tipoId,
            'numero_documento' => (string) $this->numero_documento,
            'tipos_documento_identidad_codigo' => $tipo ? (string) $tipo->codigo : null,
            'tipos_documento_identidad_nombre' => $tipo ? (string) $tipo->nombre : null,
        ];
    }
}
