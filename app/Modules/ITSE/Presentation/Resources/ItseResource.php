<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ItseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'archivos' => [],
            'historial_estados' => [],
            'giros' => [],
            'uuid' => $this->resource->uuid,
            'tipo_itse_id' => $this->resource->tipoItseId,
            'numero_itse' => $this->resource->numeroItse,
            'fecha_expedicion' => $this->resource->fechaExpedicion->format('Y-m-d'),
            'fecha_solicitud_renovacion' => $this->resource->fechaSolicitudRenovacion?->format('Y-m-d'),
            'fecha_caducidad' => $this->resource->fechaCaducidad->format('Y-m-d'),
            'nombre_comercial' => $this->resource->nombreComercial,
            'direccion' => $this->resource->direccion,
            'resolucion_numero' => $this->resource->resolucionNumero,
            'area' => (string) number_format((float) $this->resource->area, 2, '.', ''),
            'numero_recibo_pago' => $this->resource->numeroReciboPago,
            'observaciones' => $this->resource->observaciones,
            'se_puede_publicar' => $this->resource->sePuedePublicar,
            'capacidad_aforo' => $this->resource->capacidadAforo,
            'fecha_notificacion' => $this->resource->fechaNotificacion?->format('Y-m-d'),
            'fecha_digitacion' => $this->resource->fechaDigitacion
                ->setTimezone(new \DateTimeZone('America/Lima'))
                ->format('Y-m-d\TH:i:s.uP'),
            'expediente' => $this->resource->expedienteId,
            'titular' => $this->resource->titularId,
            'conductor' => $this->resource->conductorId,
            'itse_principal' => $this->resource->itsePrincipalId,
            'nivel_riesgo' => $this->resource->nivelRiesgoId,
            'usuario' => $this->resource->usuarioId,
        ];
    }
}
