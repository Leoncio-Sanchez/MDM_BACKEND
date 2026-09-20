<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Infrastructure\Persistence\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property int $plazo_atencion_dias
 * @property int $dias_alerta_vencimiento
 * @property bool $esta_activo
 * @property bool $requiere_lf
 * @property bool $requiere_itse
 * @property Carbon $fecha_digitacion
 * @property int $unidad_organica_id
 * @property int $usuario_id
 */
class TipoProcedimientoTupaModel extends Model
{
    protected $table = 'tipos_procedimiento_tupa';

    public $timestamps = false;

    protected $fillable = [
        'codigo', 'nombre', 'plazo_atencion_dias', 'dias_alerta_vencimiento',
        'esta_activo', 'requiere_lf', 'requiere_itse', 'fecha_digitacion',
        'unidad_organica_id', 'usuario_id',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
        'requiere_lf' => 'boolean',
        'requiere_itse' => 'boolean',
        'fecha_digitacion' => 'datetime',
    ];
}
