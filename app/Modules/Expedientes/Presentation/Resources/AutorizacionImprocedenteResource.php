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
        $tz = new \DateTimeZone('America/Lima');
        $fmtDigitacion = ((int) $this->fechaDigitacion->format('u')) > 0 ? 'Y-m-d\TH:i:s.uP' : 'Y-m-d\TH:i:sP';

        return [
            'id' => $this->id,
            'tipo_autorizacion' => $this->tipoAutorizacion->value,
            'fecha_rechazo' => $this->fechaRechazo->format('Y-m-d'),
            'documento' => $this->documento,
            'observaciones' => $this->observaciones,
            'fecha_digitacion' => $this->fechaDigitacion
                ->setTimezone($tz)
                ->format($fmtDigitacion),
            'expediente' => $this->expedienteId,
            'usuario' => $this->usuarioId,
        ];
    }
}
