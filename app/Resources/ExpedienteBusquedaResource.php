<?php

declare(strict_types=1);

namespace App\Resources;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

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
            'id' => (int) data_get($data, 'id'),
            'tipo_procedimiento_tupa_id' => (int) data_get($data, 'tipo_procedimiento_tupa_id'),
            'numero_expediente' => (int) data_get($data, 'numero_expediente'),
            'fecha_recepcion' => $this->formatUtcIso8601(data_get($data, 'fecha_recepcion')),
            'solicitante_id' => (int) data_get($data, 'solicitante_id'),
            'representante_id' => data_get($data, 'representante_id') !== null
                ? (int) data_get($data, 'representante_id')
                : null,
            'observaciones' => data_get($data, 'observaciones') !== null
                ? (string) data_get($data, 'observaciones')
                : null,
            'fecha_vencimiento' => $this->formatDate(data_get($data, 'fecha_vencimiento')),
            'fecha_alerta' => $this->formatDate(data_get($data, 'fecha_alerta')),
            'fecha_suspension' => $this->formatDate(data_get($data, 'fecha_suspension')),
            'dias_ampliacion' => data_get($data, 'dias_ampliacion') !== null
                ? (int) data_get($data, 'dias_ampliacion')
                : null,
            'motivo_ampliacion' => data_get($data, 'motivo_ampliacion') !== null
                ? (string) data_get($data, 'motivo_ampliacion')
                : null,
            'usuario_ampliacion' => data_get($data, 'usuario_ampliacion') !== null
                ? (int) data_get($data, 'usuario_ampliacion')
                : null,
            'fecha_digitacion_ampliacion' => $this->formatUtcIso8601(data_get($data, 'fecha_digitacion_ampliacion')),
            'usuario_id' => (int) data_get($data, 'usuario_id'),
            'fecha_digitacion' => $this->formatUtcIso8601(data_get($data, 'fecha_digitacion')),
            'nombre_procedimiento' => data_get($data, 'nombre_procedimiento'),
            'plazo_atencion_dias' => (int) data_get($data, 'plazo_atencion_dias'),
            'dias_alerta_vencimiento' => (int) data_get($data, 'dias_alerta_vencimiento'),
            'requiere_itse' => (bool) data_get($data, 'requiere_itse'),
            'requiere_lf' => (bool) data_get($data, 'requiere_lf'),
            'solicitante_nombre' => data_get($data, 'solicitante_nombre'),
            'solicitante_ruc' => data_get($data, 'solicitante_ruc') !== null
                ? (string) data_get($data, 'solicitante_ruc')
                : null,
            'licencia_pendiente' => (bool) data_get($data, 'licencia_pendiente'),
            'itse_pendiente' => (bool) data_get($data, 'itse_pendiente'),
            'mostrar_alerta' => (bool) data_get($data, 'mostrar_alerta'),
            'dias_habiles_restantes' => data_get($data, 'dias_habiles_restantes') !== null
                ? (int) data_get($data, 'dias_habiles_restantes')
                : null,
        ];
    }

    private function formatUtcIso8601(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            $dt = DateTimeImmutable::createFromInterface($value)->setTimezone(new DateTimeZone('UTC'));
        } else {
            try {
                $dt = (new DateTimeImmutable((string) $value))->setTimezone(new DateTimeZone('UTC'));
            } catch (Throwable) {
                return (string) $value;
            }
        }

        $micro = (int) $dt->format('u');
        if ($micro > 0) {
            $ms = substr($dt->format('u'), 0, 3);

            return $dt->format('Y-m-d\TH:i:s.').$ms.'Z';
        }

        return $dt->format('Y-m-d\TH:i:s\Z');
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10);
    }
}
