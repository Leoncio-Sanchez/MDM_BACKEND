<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Presentation\Resources;

use App\Modules\TipoProcedimientoTupa\Domain\Entities\TipoProcedimientoTupa;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read TipoProcedimientoTupa $resource
 */
class TipoProcedimientoTupaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'codigo' => $this->resource->codigo,
            'nombre' => $this->resource->nombre,
            'plazo_atencion_dias' => $this->resource->plazoAtencionDias,
            'dias_alerta_vencimiento' => $this->resource->diasAlertaVencimiento,
            'esta_activo' => $this->resource->estaActivo,
            'requiere_lf' => $this->resource->requiereLf,
            'requiere_itse' => $this->resource->requiereItse,
            'fecha_digitacion' => $this->resource->fechaDigitacion->setTimezone(new \DateTimeZone('America/Lima'))->format('Y-m-d\TH:i:s.uP'),
            'unidad_organica' => $this->resource->unidadOrganicaId,
            'usuario' => $this->resource->usuarioId,
        ];
    }
}
