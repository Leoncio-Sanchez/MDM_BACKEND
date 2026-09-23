<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ExpedienteBusquedaResource extends JsonResource
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
            'tipo_procedimiento_tupa_id' => data_get($data, 'tipo_procedimiento_tupa_id'),
            'numero_expediente' => data_get($data, 'numero_expediente'),
            'fecha_recepcion' => data_get($data, 'fecha_recepcion'),
            'solicitante_id' => data_get($data, 'solicitante_id'),
            'representante_id' => data_get($data, 'representante_id'),
            'observaciones' => data_get($data, 'observaciones'),
            'fecha_vencimiento' => data_get($data, 'fecha_vencimiento'),
            'fecha_alerta' => data_get($data, 'fecha_alerta'),
            'fecha_suspension' => data_get($data, 'fecha_suspension'),
            'dias_ampliacion' => data_get($data, 'dias_ampliacion'),
            'motivo_ampliacion' => data_get($data, 'motivo_ampliacion'),
            'usuario_ampliacion' => data_get($data, 'usuario_ampliacion'),
            'fecha_digitacion_ampliacion' => data_get($data, 'fecha_digitacion_ampliacion'),
            'usuario_id' => data_get($data, 'usuario_id'),
            'fecha_digitacion' => data_get($data, 'fecha_digitacion'),
            'nombre_procedimiento' => data_get($data, 'nombre_procedimiento'),
            'plazo_atencion_dias' => data_get($data, 'plazo_atencion_dias'),
            'dias_alerta_vencimiento' => data_get($data, 'dias_alerta_vencimiento'),
            'requiere_itse' => (bool) data_get($data, 'requiere_itse'),
            'requiere_lf' => (bool) data_get($data, 'requiere_lf'),
            'solicitante_nombre' => data_get($data, 'solicitante_nombre'),
            'solicitante_ruc' => data_get($data, 'solicitante_ruc'),
            'licencia_pendiente' => (bool) data_get($data, 'licencia_pendiente'),
            'itse_pendiente' => (bool) data_get($data, 'itse_pendiente'),
            'mostrar_alerta' => (bool) data_get($data, 'mostrar_alerta'),
        ];
    }
}
