<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ExpedientePendienteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed>|object $data */
        $data = $this->resource;

        return [
            'id' => data_get($data, 'id'),
            'numero_expediente' => data_get($data, 'numero_expediente'),
            'fecha_recepcion' => data_get($data, 'fecha_recepcion'),
            'nombre' => data_get($data, 'nombre'),
            'fecha_vencimiento' => data_get($data, 'fecha_vencimiento'),
            'fecha_alerta' => data_get($data, 'fecha_alerta'),
            'persona_nombre' => data_get($data, 'persona_nombre'),
            'licencia_pendiente' => (bool) data_get($data, 'licencia_pendiente'),
            'itse_pendiente' => (bool) data_get($data, 'itse_pendiente'),
            'mostrar_alerta' => (bool) data_get($data, 'mostrar_alerta'),
            'dias_habiles_restantes' => data_get($data, 'dias_habiles_restantes'),
        ];
    }
}
