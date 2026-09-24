<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\TipoProcedimientoTupa;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TipoProcedimientoTupa
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property int $plazo_atencion_dias
 * @property int $dias_alerta_vencimiento
 * @property bool $esta_activo
 * @property bool $requiere_lf
 * @property bool $requiere_itse
 * @property Carbon|DateTimeInterface|string|null $fecha_digitacion
 * @property int $unidad_organica_id
 * @property int $usuario_id
 */
class TipoProcedimientoTupaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $fecha = $this->fecha_digitacion;
        if ($fecha instanceof Carbon) {
            $fechaStr = $fecha->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP');
        } elseif ($fecha instanceof DateTimeInterface) {
            $fechaStr = Carbon::instance($fecha)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP');
        } elseif (is_string($fecha)) {
            $fechaStr = Carbon::parse($fecha)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP');
        } else {
            $fechaStr = null;
        }

        return [
            'id' => (int) $this->id,
            'codigo' => (string) $this->codigo,
            'nombre' => (string) $this->nombre,
            'plazo_atencion_dias' => (int) $this->plazo_atencion_dias,
            'dias_alerta_vencimiento' => (int) $this->dias_alerta_vencimiento,
            'esta_activo' => (bool) $this->esta_activo,
            'requiere_lf' => (bool) $this->requiere_lf,
            'requiere_itse' => (bool) $this->requiere_itse,
            'fecha_digitacion' => $fechaStr,
            'unidad_organica' => (int) $this->unidad_organica_id,
            'usuario' => (int) $this->usuario_id,
        ];
    }
}
