<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\LicenciaFuncionamiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class LicenciaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $id = null;
        if ($this->resource instanceof LicenciaFuncionamiento) {
            $id = $this->resource->id;
        } elseif (is_array($this->resource)) {
            $id = $this->resource['id'] ?? null;
        } elseif (is_object($this->resource) && isset($this->resource->id)) {
            $id = $this->resource->id;
        }

        if (! $id && ! ($this->resource instanceof LicenciaFuncionamiento)) {
            return [];
        }

        $model = $this->resource instanceof LicenciaFuncionamiento
            ? $this->resource
            : LicenciaFuncionamiento::find($id);

        if (! $model instanceof LicenciaFuncionamiento) {
            return [];
        }

        $realId = $model->id;

        $giros = DB::table('licencias_funcionamiento_giros')
            ->where('licencia_funcionamiento_id', $realId)
            ->select([
                'id',
                'licencia_funcionamiento_id as licencia_funcionamiento',
                'giro_id as giro',
                'usuario_id as usuario',
                'fecha_digitacion',
            ])
            ->get()
            ->map(function ($g) {
                $g->fecha_digitacion = $g->fecha_digitacion
                    ? Carbon::parse($g->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP')
                    : null;

                return (array) $g;
            })
            ->toArray();

        $archivos = DB::table('licencias_funcionamiento_archivos')
            ->where('licencia_funcionamiento_id', $realId)
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
                'fecha_digitacion',
            ])
            ->get()
            ->map(function ($a) {
                $a->fecha_digitacion = $a->fecha_digitacion
                    ? Carbon::parse($a->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP')
                    : null;

                return (array) $a;
            })
            ->toArray();

        $historialEstados = DB::table('licencias_funcionamiento_estados')
            ->where('licencia_funcionamiento_id', $realId)
            ->select([
                'id',
                'licencia_funcionamiento_id as licencia_funcionamiento',
                'estado_id as estado',
                'fecha_estado',
                'documento',
                'observaciones',
                'usuario_id as usuario',
                'fecha_digitacion',
            ])
            ->get()
            ->map(function ($h) {
                $h->fecha_digitacion = $h->fecha_digitacion
                    ? Carbon::parse($h->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP')
                    : null;
                $h->fecha_estado = $h->fecha_estado
                    ? Carbon::parse($h->fecha_estado)->toDateString()
                    : null;

                return (array) $h;
            })
            ->toArray();

        return [
            'id' => $model->id,
            'uuid' => (string) ($model->uuid ?? ''),
            'expediente' => $model->expediente_id,
            'tipo_licencia' => $model->tipo_licencia_id,
            'numero_licencia' => $model->numero_licencia,
            'fecha_emision' => $model->fecha_emision ? Carbon::parse($model->fecha_emision)->toDateString() : null,
            'titular' => $model->titular_id,
            'conductor' => $model->conductor_id,
            'licencia_principal' => $model->licencia_principal_id,
            'nombre_comercial' => (string) ($model->nombre_comercial ?? ''),
            'es_vigencia_indeterminada' => (bool) $model->es_vigencia_indeterminada,
            'fecha_inicio_vigencia' => $model->fecha_inicio_vigencia ? Carbon::parse($model->fecha_inicio_vigencia)->toDateString() : null,
            'fecha_fin_vigencia' => $model->fecha_fin_vigencia ? Carbon::parse($model->fecha_fin_vigencia)->toDateString() : null,
            'nivel_riesgo' => $model->nivel_riesgo_id,
            'actividad' => (string) ($model->actividad ?? ''),
            'direccion' => (string) ($model->direccion ?? ''),
            'hora_desde' => (int) ($model->hora_desde ?? 0),
            'hora_hasta' => (int) ($model->hora_hasta ?? 0),
            'resolucion_numero' => (string) ($model->resolucion_numero ?? ''),
            'zonificacion' => $model->zonificacion_id,
            'area' => number_format((float) ($model->area ?? 0), 2, '.', ''),
            'numero_recibo_pago' => (string) ($model->numero_recibo_pago ?? ''),
            'observaciones' => $model->observaciones !== null ? (string) $model->observaciones : '',
            'se_puede_publicar' => (bool) $model->se_puede_publicar,
            'fecha_notificacion' => $model->fecha_notificacion ? Carbon::parse($model->fecha_notificacion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP') : null,
            'usuario' => $model->usuario_id,
            'fecha_digitacion' => $model->fecha_digitacion ? Carbon::parse($model->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP') : null,
            'archivos' => $archivos,
            'historial_estados' => $historialEstados,
            'giros' => $giros,
        ];
    }
}
