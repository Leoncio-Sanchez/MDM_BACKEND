<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Resources;

use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AutorizacionImprocedente
 */
final class AutorizacionImprocedenteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_autorizacion' => $this->tipoAutorizacion->value,
            'fecha_rechazo' => $this->fechaRechazo->format('Y-m-d'),
            'documento' => $this->documento,
            'observaciones' => $this->observaciones,
            'fecha_digitacion' => $this->fechaDigitacion
                ->setTimezone(new \DateTimeZone('America/Lima'))
                ->format('Y-m-d\TH:i:s.uP'),
            'expediente' => $this->expedienteId,
            'usuario' => $this->usuarioId,
        ];
    }
}
