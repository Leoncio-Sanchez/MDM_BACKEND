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
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'numero_expediente' => $this->numeroExpediente,
            'fecha_recepcion' => $this->fechaRecepcion
                ->setTimezone(new \DateTimeZone('America/Lima'))
                ->format('Y-m-d\TH:i:s.uP'),
            'fecha_vencimiento' => $this->fechaVencimiento->format('Y-m-d'),
            'fecha_alerta' => $this->fechaAlerta->format('Y-m-d'),
            'tipo_procedimiento_tupa' => $this->tipoProcedimientoTupaId,
            'solicitante' => $this->solicitanteId,
        ];
    }
}
