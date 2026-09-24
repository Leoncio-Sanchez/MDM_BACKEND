<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel;
use Illuminate\Support\Facades\DB;

class LicenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $id = null;
        if ($this->resource instanceof Licencia) {
            $id = $this->resource->id;
        } elseif ($this->resource instanceof LicenciaModel) {
            $id = $this->resource->id;
        } elseif (is_array($this->resource)) {
            $id = $this->resource['id'] ?? null;
        } elseif (is_object($this->resource) && isset($this->resource->id)) {
            $id = $this->resource->id;
        }

        $giros = [];
        $archivos = [];
        $historial_estados = [];

        if ($id) {
            $giros = DB::table('licencias_funcionamiento_giros')
                ->where('licencia_funcionamiento_id', $id)
                ->select([
                    'id',
                    'licencia_funcionamiento_id as licencia_funcionamiento',
                    'giro_id as giro',
                    'usuario_id as usuario',
                    'fecha_digitacion'
                ])
                ->get()
                ->map(function ($g) {
                    $g->fecha_digitacion = \Carbon\Carbon::parse($g->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-dTH:i:s.uP');
                    return $g;
                })
                ->toArray();

            $archivos = DB::table('licencias_funcionamiento_archivos')
                ->where('licencia_funcionamiento_id', $id)
                ->select([
                    'id',
                    'uuid',
                    'licencia_funcionamiento_id as licencia_funcionamiento',
                    'nombre_original',
                    'nombre_archivo',
                    'ruta_archivo',
                    'extension',
                    'tamanio_bytes',
                    'usuario_id as usuario',
                    'fecha_digitacion'
                ])
                ->get()
                ->map(function ($a) {
                    $a->fecha_digitacion = \Carbon\Carbon::parse($a->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-dTH:i:s.uP');
                    return $a;
                })
                ->toArray();

            $historial_estados = DB::table('licencias_funcionamiento_estados')
                ->where('licencia_funcionamiento_id', $id)
                ->select([
                    'id',
                    'licencia_funcionamiento_id as licencia_funcionamiento',
                    'estado_id as estado',
                    'fecha_estado',
                    'documento',
                    'observaciones',
                    'usuario_id as usuario',
                    'fecha_digitacion'
                ])
                ->get()
                ->map(function ($h) {
                    $h->fecha_digitacion = \Carbon\Carbon::parse($h->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-dTH:i:s.uP');
                    return $h;
                })
                ->toArray();
        }

        $model = $this->resource instanceof LicenciaModel ? $this->resource : ($id ? LicenciaModel::find($id) : null);

        if (! $model) {
            return [];
        }

        return [
            'id' => $model?->id,
            'uuid' => $model?->uuid,
            'expediente' => $model?->expediente_id,
            'tipo_licencia' => $model?->tipo_licencia_id,
            'numero_licencia' => $model?->numero_licencia,
            'fecha_emision' => $model?->fecha_emision ? \Carbon\Carbon::parse($model->fecha_emision)->toDateString() : null,
            'titular' => $model?->titular_id,
            'conductor' => $model?->conductor_id,
            'licencia_principal' => $model?->licencia_principal_id,
            'nombre_comercial' => $model?->nombre_comercial,
            'es_vigencia_indeterminada' => (bool) $model?->es_vigencia_indeterminada,
            'fecha_inicio_vigencia' => $model?->fecha_inicio_vigencia ? \Carbon\Carbon::parse($model->fecha_inicio_vigencia)->toDateString() : null,
            'fecha_fin_vigencia' => $model?->fecha_fin_vigencia ? \Carbon\Carbon::parse($model->fecha_fin_vigencia)->toDateString() : null,
            'nivel_riesgo' => $model?->nivel_riesgo_id,
            'actividad' => $model?->actividad,
            'direccion' => $model?->direccion,
            'hora_desde' => (int) $model?->hora_desde,
            'hora_hasta' => (int) $model?->hora_hasta,
            'resolucion_numero' => $model?->resolucion_numero,
            'zonificacion' => $model?->zonificacion_id,
            'area' => $model ? number_format((float) ($model->area ?? 0), 2, '.', '') : '0.00',
            'numero_recibo_pago' => $model?->numero_recibo_pago,
            'observaciones' => $model?->observaciones,
            'se_puede_publicar' => (bool) $model?->se_puede_publicar,
            'fecha_notificacion' => $model?->fecha_notificacion ? \Carbon\Carbon::parse($model->fecha_notificacion)->toIso8601String() : null,
            'usuario' => $model?->usuario_id,
            'fecha_digitacion' => $model?->fecha_digitacion ? \Carbon\Carbon::parse($model->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-dTH:i:s.uP') : null,
            'archivos' => $archivos,
            'historial_estados' => $historial_estados,
            'giros' => $giros,
        ];
    }
}
