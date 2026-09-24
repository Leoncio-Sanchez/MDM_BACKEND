<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\Zonificacion;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Zonificacion
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $esta_activo
 * @property Carbon|DateTimeInterface|string|null $fecha_digitacion
 * @property int $usuario_id
 */
class ZonificacionResource extends JsonResource
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
            'esta_activo' => (bool) $this->esta_activo,
            'fecha_digitacion' => $fechaStr,
            'usuario' => (int) $this->usuario_id,
        ];
    }
}
