<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EstadoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $nombre
 * @property bool $es_para_lf
 * @property bool $es_para_itse
 * @property bool $esta_activo
 *
 * @method static Builder|Estado query()
 * @method static Builder|Estado activos()
 * @method static Builder|Estado inactivos()
 * @method static Builder|Estado buscarPorNombre(string $nombre)
 * @method static EstadoFactory factory($count = null, $state = [])
 */
class Estado extends Model
{
    /** @use HasFactory<EstadoFactory> */
    use HasFactory;

    protected $table = 'estados';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'es_para_lf',
        'es_para_itse',
        'esta_activo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'es_para_lf' => 'boolean',
            'es_para_itse' => 'boolean',
            'esta_activo' => 'boolean',
        ];
    }

    protected static function newFactory(): EstadoFactory
    {
        return EstadoFactory::new();
    }

    // Scopes
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

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeBuscarPorNombre(Builder $query, string $nombre): Builder
    {
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where('nombre', $like, "%{$nombre}%");
    }

    // Lógica de negocio simple
    public function estaActivo(): bool
    {
        return (bool) $this->esta_activo;
    }

    public function activar(): void
    {
        $this->update(['esta_activo' => true]);
    }

    public function desactivar(): void
    {
        $this->update(['esta_activo' => false]);
    }
}
