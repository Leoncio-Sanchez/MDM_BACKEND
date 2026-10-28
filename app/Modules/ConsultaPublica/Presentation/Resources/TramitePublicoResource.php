<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Presentation\Resources;

use App\Modules\ConsultaPublica\Domain\ValueObjects\TramitePublico;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Para expedientes: fecha_emision = fecha de ingreso y fecha_vencimiento = fecha límite de atención.
 * Para licencias e ITSE: fechas del documento emitido.
 *
 * @property TramitePublico $resource
 */
final class TramitePublicoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $t = $this->resource;

        return [
            'tipo' => $t->tipo->value,
            'numero' => $t->numero,
            'estado' => $t->estado,
            'nombre_comercial' => $t->nombreComercial,
            'procedimiento' => $t->procedimiento,
            'giros' => $t->giros,
            'fecha_emision' => $t->fechaEmision?->format('Y-m-d'),
            'fecha_vencimiento' => $t->fechaVencimiento?->format('Y-m-d'),
            'vigencia_indeterminada' => $t->vigenciaIndeterminada,
        ];
    }
}
