<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LicenciaListResource extends JsonResource
{
    /**
     * Disable the data wrapper
     */
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'numero_licencia' => $this->numero_licencia,
            'fecha_emision' => $this->fecha_emision,
            'nombre_comercial' => $this->nombre_comercial,
            'titular' => $this->titular_id,
            'expediente' => $this->expediente_id,
        ];
    }
}
