<?php

declare(strict_types=1);

namespace App\Resources;

use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ExpedienteListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed>|object $model */
        $model = $this->resource;

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

        return [
            'id' => (int) data_get($model, 'id'),
            'uuid' => (string) (data_get($model, 'uuid') ?? ''),
            'numero_expediente' => (int) data_get($model, 'numero_expediente'),
            'fecha_recepcion' => $formatDateTime(data_get($model, 'fecha_recepcion')),
            'fecha_vencimiento' => $formatDate(data_get($model, 'fecha_vencimiento')),
            'fecha_alerta' => $formatDate(data_get($model, 'fecha_alerta')),
            'tipo_procedimiento_tupa' => (int) $tipoProcedimientoTupaId,
            'solicitante' => (int) $solicitanteId,
        ];
    }
}
