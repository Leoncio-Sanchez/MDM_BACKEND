<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Resources;

use App\Modules\Expedientes\Domain\Entities\Expediente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Expediente
 */
final class ExpedienteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'archivos' => [],  // FASE 9
            'autorizaciones_improcedentes' => $this->autorizacionesImprocedentes
                ? AutorizacionImprocedenteResource::collection($this->autorizacionesImprocedentes)->resolve()
                : [],
            'uuid' => $this->uuid,
            'numero_expediente' => $this->numeroExpediente,
            'fecha_recepcion' => $this->fechaRecepcion
                ->setTimezone(new \DateTimeZone('America/Lima'))
                ->format('Y-m-d\TH:i:s.uP'),
            'observaciones' => $this->observaciones,
            'fecha_vencimiento' => $this->fechaVencimiento->format('Y-m-d'),
            'fecha_alerta' => $this->fechaAlerta->format('Y-m-d'),
            'fecha_suspension' => $this->fechaSuspension?->format('Y-m-d'),
            'dias_ampliacion' => $this->diasAmpliacion,
            'motivo_ampliacion' => $this->motivoAmpliacion,
            'fecha_digitacion_ampliacion' => $this->fechaDigitacionAmpliacion
                ?->setTimezone(new \DateTimeZone('America/Lima'))
                ->format('Y-m-d\TH:i:s.uP'),
            'fecha_digitacion' => $this->fechaDigitacion
                ->setTimezone(new \DateTimeZone('America/Lima'))
                ->format('Y-m-d\TH:i:s.uP'),
            'tipo_procedimiento_tupa' => $this->tipoProcedimientoTupaId,
            'solicitante' => $this->solicitanteId,
            'representante' => $this->representanteId,
            'usuario_ampliacion' => $this->usuarioAmpliacionId,
            'usuario' => $this->usuarioId,
        ];
    }
}
