<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Resources;

use App\Modules\Expedientes\Domain\Entities\Expediente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Expediente
 */
final class ExpedienteListResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $tz = new \DateTimeZone('America/Lima');
        $fmtRecepcion = ((int) $this->fechaRecepcion->format('u')) > 0 ? 'Y-m-d\TH:i:s.uP' : 'Y-m-d\TH:i:sP';

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'numero_expediente' => $this->numeroExpediente,
            'fecha_recepcion' => $this->fechaRecepcion
                ->setTimezone($tz)
                ->format($fmtRecepcion),
            'fecha_vencimiento' => $this->fechaVencimiento->format('Y-m-d'),
            'fecha_alerta' => $this->fechaAlerta->format('Y-m-d'),
            'tipo_procedimiento_tupa' => $this->tipoProcedimientoTupaId,
            'solicitante' => $this->solicitanteId,
            'anulado' => $this->estaAnulado(),
            'anulacion' => AnulacionExpedienteResource::oNulo($this->anulacion),
        ];
    }
}
