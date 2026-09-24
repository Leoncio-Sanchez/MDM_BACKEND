<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UnidadOrganicaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $sigla
 * @property bool $esta_activo
 *
 * @method static Builder<static>|UnidadOrganica activos()
 * @method static Builder<static>|UnidadOrganica inactivos()
 * @method static UnidadOrganica create(array<string, mixed> $attributes = [])
 * @method static UnidadOrganica findOrFail(mixed $id, array<string> $columns = ['*'])
 * @method static Builder<static>|UnidadOrganica query()
 */
class UnidadOrganica extends Model
{
    /** @use HasFactory<UnidadOrganicaFactory> */
    use HasFactory;

    protected $table = 'unidades_organicas';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'sigla',
        'esta_activo',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
    ];

    public function estaActivo(): bool
    {
        return (bool) $this->esta_activo;
    }

    /**
     * @param  Builder<UnidadOrganica>  $query
     * @return Builder<UnidadOrganica>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('esta_activo', true);
    }

    /**
     * @param  Builder<UnidadOrganica>  $query
     * @return Builder<UnidadOrganica>
     */
    public function scopeInactivos(Builder $query): Builder
    {
        return $query->where('esta_activo', false);
    }

    protected static function newFactory(): UnidadOrganicaFactory
    {
        return UnidadOrganicaFactory::new();
    }
}
