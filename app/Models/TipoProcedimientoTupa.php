<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TipoProcedimientoTupaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property Carbon|null $fecha_digitacion
 * @property int $unidad_organica_id
 * @property int $usuario_id
 *
 * @method static Builder<static>|TipoProcedimientoTupa activos()
 * @method static Builder<static>|TipoProcedimientoTupa inactivos()
 * @method static TipoProcedimientoTupa create(array<string, mixed> $attributes = [])
 * @method static TipoProcedimientoTupa findOrFail(mixed $id, array<string> $columns = ['*'])
 * @method static Builder<static>|TipoProcedimientoTupa query()
 */
class TipoProcedimientoTupa extends Model
{
    /** @use HasFactory<TipoProcedimientoTupaFactory> */
    use HasFactory;

    protected $table = 'tipos_procedimiento_tupa';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'plazo_atencion_dias',
        'dias_alerta_vencimiento',
        'esta_activo',
        'requiere_lf',
        'requiere_itse',
        'fecha_digitacion',
        'unidad_organica_id',
        'usuario_id',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
        'requiere_lf' => 'boolean',
        'requiere_itse' => 'boolean',
        'fecha_digitacion' => 'datetime',
        'plazo_atencion_dias' => 'integer',
        'dias_alerta_vencimiento' => 'integer',
        'unidad_organica_id' => 'integer',
        'usuario_id' => 'integer',
    ];

    public function estaActivo(): bool
    {
        return (bool) $this->esta_activo;
    }

    /**
     * @param  Builder<TipoProcedimientoTupa>  $query
     * @return Builder<TipoProcedimientoTupa>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('esta_activo', true);
    }

    /**
     * @param  Builder<TipoProcedimientoTupa>  $query
     * @return Builder<TipoProcedimientoTupa>
     */
    public function scopeInactivos(Builder $query): Builder
    {
        return $query->where('esta_activo', false);
    }

    protected static function newFactory(): TipoProcedimientoTupaFactory
    {
        return TipoProcedimientoTupaFactory::new();
    }
}
