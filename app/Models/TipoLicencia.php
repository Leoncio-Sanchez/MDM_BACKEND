<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TipoLicenciaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $esta_activo
 *
 * @method static Builder<self> query()
 * @method static Builder<self> activos()
 * @method static Builder<self> inactivos()
 */
class TipoLicencia extends Model
{
    /** @use HasFactory<TipoLicenciaFactory> */
    use HasFactory;

    protected static function newFactory(): TipoLicenciaFactory
    {
        return TipoLicenciaFactory::new();
    }

    protected $table = 'tipos_licencia';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'esta_activo',
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
