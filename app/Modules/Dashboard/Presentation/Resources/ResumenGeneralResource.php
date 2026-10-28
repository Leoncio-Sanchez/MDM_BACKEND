<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Presentation\Resources;

use App\Modules\Dashboard\Domain\ValueObjects\ResumenGeneral;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property ResumenGeneral $resource */
final class ResumenGeneralResource extends JsonResource
{
    /** @return array<string, array<string, int>> */
    public function toArray(Request $request): array
    {
        $r = $this->resource;

        return [
            'expedientes' => [
                'en_tramite' => $r->expedientesEnTramite,
                'por_vencer' => $r->expedientesPorVencer,
                'vencidos' => $r->expedientesVencidos,
            ],
            'licencias' => [
                'vigentes' => $r->licenciasVigentes,
                'emitidas_mes' => $r->licenciasEmitidasMes,
                'total' => $r->licenciasTotal,
            ],
            'itse' => [
                'vigentes' => $r->itseVigentes,
                'por_vencer' => $r->itsePorVencer,
                'emitidos_mes' => $r->itseEmitidosMes,
                'dias_alerta' => $r->diasAlertaItse,
            ],
        ];
    }
}
