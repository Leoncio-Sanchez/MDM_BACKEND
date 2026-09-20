<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Presentation\Resources;

use App\Modules\TipoDocumentoIdentidad\Domain\Entities\TipoDocumentoIdentidad;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read TipoDocumentoIdentidad $resource
 */
class TipoDocumentoIdentidadResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'codigo' => $this->resource->codigo,
            'nombre' => $this->resource->nombre,
            'esta_activo' => $this->resource->estaActivo,
            'es_para_natural' => $this->resource->esParaNatural,
            'es_para_juridica' => $this->resource->esParaJuridica,
        ];
    }
}
