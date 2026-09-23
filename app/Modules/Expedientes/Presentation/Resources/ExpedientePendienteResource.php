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
            'id' => (int) data_get($data, 'id'),
            'numero_expediente' => (int) data_get($data, 'numero_expediente'),
            'fecha_recepcion' => $this->formatUtcIso8601(data_get($data, 'fecha_recepcion')),
            'nombre' => data_get($data, 'nombre'),
            'fecha_vencimiento' => $this->formatDate(data_get($data, 'fecha_vencimiento')),
            'fecha_alerta' => $this->formatDate(data_get($data, 'fecha_alerta')),
            'persona_nombre' => data_get($data, 'persona_nombre'),
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

        if ($value instanceof \DateTimeInterface) {
            $dt = \DateTimeImmutable::createFromInterface($value)->setTimezone(new \DateTimeZone('UTC'));
        } else {
            try {
                $dt = (new \DateTimeImmutable((string) $value))->setTimezone(new \DateTimeZone('UTC'));
            } catch (\Throwable) {
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

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10);
    }
}
