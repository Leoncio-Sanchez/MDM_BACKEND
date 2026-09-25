<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\LicenciaFuncionamiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LicenciaReporteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof LicenciaFuncionamiento) {
            return [
                'id' => $this->resource->id,
                'numero_licencia' => $this->resource->numero_licencia,
                'nombre_comercial' => $this->resource->nombre_comercial,
                'fecha_emision' => $this->resource->fecha_emision ? Carbon::parse($this->resource->fecha_emision)->toDateString() : null,
            ];
        }

        $arr = (array) $this->resource;

        return [
            'id' => $arr['id'] ?? null,
            'numero_licencia' => $arr['numero_licencia'] ?? null,
            'nombre_comercial' => $arr['nombre_comercial'] ?? null,
            'fecha_emision' => ! empty($arr['fecha_emision']) ? Carbon::parse($arr['fecha_emision'])->toDateString() : null,
        ];
    }
}
