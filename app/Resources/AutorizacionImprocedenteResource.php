<?php

declare(strict_types=1);

namespace App\Resources;

use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AutorizacionImprocedenteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed>|object $data */
        $data = $this->resource;

        $id = data_get($data, 'id');
        $tipo = data_get($data, 'tipo_autorizacion');
        if ($tipo instanceof \BackedEnum) {
            $tipo = $tipo->value;
        }

        $fechaRechazo = data_get($data, 'fecha_rechazo');
        $fechaRechazoStr = null;
        if ($fechaRechazo !== null && $fechaRechazo !== '') {
            $fechaRechazoStr = substr((string) ($fechaRechazo instanceof DateTimeInterface ? $fechaRechazo->format('Y-m-d') : $fechaRechazo), 0, 10);
        }

        $fechaDigitacion = data_get($data, 'fecha_digitacion');
        $fechaDigitacionStr = null;
        if ($fechaDigitacion !== null && $fechaDigitacion !== '') {
            $c = Carbon::parse($fechaDigitacion)->setTimezone(new DateTimeZone('America/Lima'));
            $fmt = ((int) $c->format('u')) > 0 ? 'Y-m-d\TH:i:s.uP' : 'Y-m-d\TH:i:sP';
            $fechaDigitacionStr = $c->format($fmt);
        }

        $expedienteId = data_get($data, 'expediente_id') ?? data_get($data, 'expedienteId') ?? data_get($data, 'expediente');
        if (is_object($expedienteId) && isset($expedienteId->id)) {
            $expedienteId = $expedienteId->id;
        }

        $usuarioId = data_get($data, 'usuario_id') ?? data_get($data, 'usuarioId') ?? data_get($data, 'usuario');
        if (is_object($usuarioId) && isset($usuarioId->id)) {
            $usuarioId = $usuarioId->id;
        }

        return [
            'id' => (int) $id,
            'tipo_autorizacion' => (string) $tipo,
            'fecha_rechazo' => $fechaRechazoStr,
            'documento' => (string) data_get($data, 'documento'),
            'observaciones' => (string) data_get($data, 'observaciones'),
            'fecha_digitacion' => $fechaDigitacionStr,
            'expediente' => (int) $expedienteId,
            'usuario' => $usuarioId !== null ? (int) $usuarioId : null,
        ];
    }
}
