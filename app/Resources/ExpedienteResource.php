<?php

declare(strict_types=1);

namespace App\Resources;

use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

final class ExpedienteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed>|object $model */
        $model = $this->resource;

        $id = (int) data_get($model, 'id');
        $tz = new DateTimeZone('America/Lima');

        $formatDateTime = function (mixed $value) use ($tz): ?string {
            if ($value === null || $value === '') {
                return null;
            }
            $c = Carbon::parse($value)->setTimezone($tz);
            $fmt = ((int) $c->format('u')) > 0 ? 'Y-m-d\TH:i:s.uP' : 'Y-m-d\TH:i:sP';

            return $c->format($fmt);
        };

        $formatDate = function (mixed $value): ?string {
            if ($value === null || $value === '') {
                return null;
            }
            if ($value instanceof DateTimeInterface) {
                return $value->format('Y-m-d');
            }

            return substr((string) $value, 0, 10);
        };

        $archivos = [];
        if (DB::getSchemaBuilder()->hasTable('expedientes_archivos')) {
            $archivos = DB::table('expedientes_archivos')
                ->where('expediente_id', $id)
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($a) use ($formatDateTime) {
                    $a = (object) $a;
                    $a->fecha_digitacion = $formatDateTime($a->fecha_digitacion ?? null);

                    return (array) $a;
                })
                ->toArray();
        }

        $autorizaciones = [];
        if (DB::getSchemaBuilder()->hasTable('autorizaciones_improcedentes')) {
            $autorizaciones = DB::table('autorizaciones_improcedentes')
                ->where('expediente_id', $id)
                ->orderBy('id', 'asc')
                ->get();
        }

        $autorizacionesData = AutorizacionImprocedenteResource::collection($autorizaciones)->resolve();

        $tipoProcedimientoTupaId = data_get($model, 'tipo_procedimiento_tupa_id')
            ?? data_get($model, 'tipoProcedimientoTupaId')
            ?? data_get($model, 'tipo_procedimiento_tupa');
        if (is_object($tipoProcedimientoTupaId) && isset($tipoProcedimientoTupaId->id)) {
            $tipoProcedimientoTupaId = $tipoProcedimientoTupaId->id;
        }

        $solicitanteId = data_get($model, 'solicitante_id')
            ?? data_get($model, 'solicitanteId')
            ?? data_get($model, 'solicitante');
        if (is_object($solicitanteId) && isset($solicitanteId->id)) {
            $solicitanteId = $solicitanteId->id;
        }

        $representanteId = data_get($model, 'representante_id')
            ?? data_get($model, 'representanteId')
            ?? data_get($model, 'representante');
        if (is_object($representanteId) && isset($representanteId->id)) {
            $representanteId = $representanteId->id;
        }

        $usuarioAmpliacionId = data_get($model, 'usuario_ampliacion_id')
            ?? data_get($model, 'usuarioAmpliacionId')
            ?? data_get($model, 'usuario_ampliacion');
        if (is_object($usuarioAmpliacionId) && isset($usuarioAmpliacionId->id)) {
            $usuarioAmpliacionId = $usuarioAmpliacionId->id;
        }

        $usuarioId = data_get($model, 'usuario_id')
            ?? data_get($model, 'usuarioId')
            ?? data_get($model, 'usuario');
        if (is_object($usuarioId) && isset($usuarioId->id)) {
            $usuarioId = $usuarioId->id;
        }

        return [
            'id' => $id,
            'archivos' => $archivos,
            'autorizaciones_improcedentes' => $autorizacionesData,
            'uuid' => (string) (data_get($model, 'uuid') ?? ''),
            'numero_expediente' => (int) data_get($model, 'numero_expediente'),
            'fecha_recepcion' => $formatDateTime(data_get($model, 'fecha_recepcion')),
            'observaciones' => data_get($model, 'observaciones') !== null ? (string) data_get($model, 'observaciones') : '',
            'fecha_vencimiento' => $formatDate(data_get($model, 'fecha_vencimiento')),
            'fecha_alerta' => $formatDate(data_get($model, 'fecha_alerta')),
            'fecha_suspension' => $formatDate(data_get($model, 'fecha_suspension')),
            'dias_ampliacion' => data_get($model, 'dias_ampliacion') !== null ? (int) data_get($model, 'dias_ampliacion') : null,
            'motivo_ampliacion' => data_get($model, 'motivo_ampliacion') !== null ? (string) data_get($model, 'motivo_ampliacion') : null,
            'fecha_digitacion_ampliacion' => $formatDateTime(data_get($model, 'fecha_digitacion_ampliacion')),
            'fecha_digitacion' => $formatDateTime(data_get($model, 'fecha_digitacion')),
            'tipo_procedimiento_tupa' => (int) $tipoProcedimientoTupaId,
            'solicitante' => (int) $solicitanteId,
            'representante' => $representanteId !== null ? (int) $representanteId : null,
            'usuario_ampliacion' => $usuarioAmpliacionId !== null ? (int) $usuarioAmpliacionId : null,
            'usuario' => $usuarioId !== null ? (int) $usuarioId : null,
        ];
    }
}
