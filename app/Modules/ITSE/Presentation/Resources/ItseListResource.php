<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ItseListResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        // El UseCase retorna array, pero JsonResource envuelve un objeto o array
        // Si entra array (por DB::select), usamos acceso array $this['id']
        // Si entra Entity Itse, usamos ->id. Para ambos casos:
        
        $isArr = is_array($this->resource);
        
        return [
            'id' => $isArr ? $this['id'] : $this->resource->id,
            'uuid' => $isArr ? ($this['uuid'] ?? null) : $this->resource->uuid,
            'numero_itse' => $isArr ? $this['numero_itse'] : $this->resource->numeroItse,
            'fecha_expedicion' => $isArr ? $this['fecha_expedicion'] : $this->resource->fechaExpedicion->format('Y-m-d'),
            'fecha_caducidad' => $isArr ? $this['fecha_caducidad'] : $this->resource->fechaCaducidad->format('Y-m-d'),
            'nombre_comercial' => $isArr ? $this['nombre_comercial'] : $this->resource->nombreComercial,
            'titular' => $isArr ? $this['titular_id'] : $this->resource->titularId,
            'expediente' => $isArr ? $this['expediente_id'] : $this->resource->expedienteId,
        ];
    }
}
