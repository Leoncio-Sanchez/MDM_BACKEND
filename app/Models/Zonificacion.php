<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ZonificacionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $esta_activo
 * @property Carbon|null $fecha_digitacion
 * @property int $usuario_id
 *
 * @method static Builder<self> query()
 * @method static Builder<self> activos()
 * @method static Builder<self> inactivos()
 */
class Zonificacion extends Model
{
    /** @use HasFactory<ZonificacionFactory> */
    use HasFactory;

    protected static function newFactory(): ZonificacionFactory
    {
        return ZonificacionFactory::new();
    }

    protected $table = 'zonificaciones';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'esta_activo',
        'fecha_digitacion',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'codigo' => 'string',
            'nombre' => 'string',
            'esta_activo' => 'boolean',
            'fecha_digitacion' => 'datetime',
            'usuario_id' => 'integer',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('esta_activo', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInactivos(Builder $query): Builder
    {
        return $query->where('esta_activo', false);
    }

    public function estaActivo(): bool
    {
        return (bool) $this->esta_activo;
    }
}
