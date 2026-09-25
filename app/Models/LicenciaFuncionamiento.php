<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property int $numero_licencia
 * @property Carbon|string|null $fecha_emision
 * @property string $nombre_comercial
 * @property int|null $titular_id
 * @property int|null $conductor_id
 * @property int|null $licencia_principal_id
 * @property int|null $expediente_id
 * @property int|null $tipo_licencia_id
 * @property int|null $nivel_riesgo_id
 * @property int|null $zonificacion_id
 * @property bool $es_vigencia_indeterminada
 * @property Carbon|string|null $fecha_inicio_vigencia
 * @property Carbon|string|null $fecha_fin_vigencia
 * @property string $actividad
 * @property string $direccion
 * @property int $hora_desde
 * @property int $hora_hasta
 * @property string $resolucion_numero
 * @property float|string $area
 * @property string $numero_recibo_pago
 * @property string|null $observaciones
 * @property bool $se_puede_publicar
 * @property Carbon|string|null $fecha_notificacion
 * @property int|null $usuario_id
 * @property Carbon|string|null $fecha_digitacion
 * @property string|null $estado
 *
 * @method static Builder<self> query()
 */
class LicenciaFuncionamiento extends Model
{
    protected $table = 'licencias_funcionamiento';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_inicio_vigencia' => 'date',
            'fecha_fin_vigencia' => 'date',
            'fecha_notificacion' => 'datetime',
            'fecha_digitacion' => 'datetime',
            'es_vigencia_indeterminada' => 'boolean',
            'se_puede_publicar' => 'boolean',
            'imprime_ordenanza_horario' => 'boolean',
            'area' => 'decimal:2',
        ];
    }
}
