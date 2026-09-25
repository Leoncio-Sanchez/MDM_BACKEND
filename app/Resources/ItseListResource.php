<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\Itse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItseListResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $isArr = is_array($this->resource);

        if ($isArr) {
            $data = $this->resource;

            return [
                'id' => (int) ($data['id'] ?? 0),
                'uuid' => (string) ($data['uuid'] ?? ''),
                'numero_itse' => (int) ($data['numero_itse'] ?? 0),
                'fecha_expedicion' => ! empty($data['fecha_expedicion']) ? Carbon::parse($data['fecha_expedicion'])->toDateString() : null,
                'fecha_caducidad' => ! empty($data['fecha_caducidad']) ? Carbon::parse($data['fecha_caducidad'])->toDateString() : null,
                'nombre_comercial' => (string) ($data['nombre_comercial'] ?? ''),
                'titular' => isset($data['titular_id']) ? (int) $data['titular_id'] : ($data['titular'] ?? null),
                'expediente' => isset($data['expediente_id']) ? (int) $data['expediente_id'] : ($data['expediente'] ?? null),
            ];
        }

        if ($this->resource instanceof Itse) {
            return [
                'id' => $this->resource->id,
                'uuid' => (string) ($this->resource->uuid ?? ''),
                'numero_itse' => (int) $this->resource->numero_itse,
                'fecha_expedicion' => $this->resource->fecha_expedicion ? Carbon::parse($this->resource->fecha_expedicion)->toDateString() : null,
                'fecha_caducidad' => $this->resource->fecha_caducidad ? Carbon::parse($this->resource->fecha_caducidad)->toDateString() : null,
                'nombre_comercial' => (string) ($this->resource->nombre_comercial ?? ''),
                'titular' => $this->resource->titular_id,
                'expediente' => $this->resource->expediente_id,
            ];
        }

        return [];
    }
}
