<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\Itse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ItseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $id = null;
        if ($this->resource instanceof Itse) {
            $id = $this->resource->id;
        } elseif (is_array($this->resource)) {
            $id = $this->resource['id'] ?? null;
        } elseif (is_object($this->resource) && isset($this->resource->id)) {
            $id = $this->resource->id;
        }

        if (! $id && ! ($this->resource instanceof Itse)) {
            return [];
        }

        $model = $this->resource instanceof Itse
            ? $this->resource
            : Itse::find($id);

        if (! $model instanceof Itse) {
            return [];
        }

        $realId = $model->id;

        $giros = DB::table('itse_giros')
            ->where('itse_id', $realId)
            ->select([
                'id',
                'itse_id as itse',
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

        $archivos = DB::table('itse_archivos')
            ->where('itse_id', $realId)
            ->select([
                'id',
                'uuid',
                'itse_id as itse',
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

        $historialEstados = DB::table('itse_estados')
            ->where('itse_id', $realId)
            ->select([
                'id',
                'itse_id as itse',
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
            'tipo_itse_id' => (int) $model->tipo_itse_id,
            'numero_itse' => (int) $model->numero_itse,
            'fecha_expedicion' => $model->fecha_expedicion ? Carbon::parse($model->fecha_expedicion)->toDateString() : null,
            'fecha_solicitud_renovacion' => $model->fecha_solicitud_renovacion ? Carbon::parse($model->fecha_solicitud_renovacion)->toDateString() : null,
            'fecha_caducidad' => $model->fecha_caducidad ? Carbon::parse($model->fecha_caducidad)->toDateString() : null,
            'titular' => $model->titular_id,
            'conductor' => $model->conductor_id,
            'itse_principal' => $model->itse_principal_id,
            'nombre_comercial' => (string) ($model->nombre_comercial ?? ''),
            'nivel_riesgo' => $model->nivel_riesgo_id,
            'direccion' => (string) ($model->direccion ?? ''),
            'resolucion_numero' => (string) ($model->resolucion_numero ?? ''),
            'area' => number_format((float) ($model->area ?? 0), 2, '.', ''),
            'numero_recibo_pago' => (string) ($model->numero_recibo_pago ?? ''),
            'observaciones' => $model->observaciones !== null ? (string) $model->observaciones : '',
            'se_puede_publicar' => (bool) $model->se_puede_publicar,
            'capacidad_aforo' => (int) ($model->capacidad_aforo ?? 0),
            'fecha_notificacion' => $model->fecha_notificacion ? Carbon::parse($model->fecha_notificacion)->toDateString() : null,
            'usuario' => $model->usuario_id,
            'fecha_digitacion' => $model->fecha_digitacion ? Carbon::parse($model->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP') : null,
            'archivos' => $archivos,
            'historial_estados' => $historialEstados,
            'giros' => $giros,
        ];
    }
}
