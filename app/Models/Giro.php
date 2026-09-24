<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\GiroFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int|null $ciiu_id
 * @property string $nombre
 * @property bool $esta_activo
 * @property Carbon|null $fecha_digitacion
 * @property int $usuario_id
 *
 * @method static Builder<self> query()
 * @method static Builder<self> activos()
 * @method static Builder<self> inactivos()
 * @method static Builder<self> buscarPorNombre(string $nombre)
 */
class Giro extends Model
{
    /** @use HasFactory<GiroFactory> */
    use HasFactory;

    protected static function newFactory(): GiroFactory
    {
        return GiroFactory::new();
    }

    protected $table = 'giros';

    public $timestamps = false;

    protected $fillable = [
        'ciiu_id',
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
            'ciiu_id' => 'integer',
            'esta_activo' => 'boolean',
            'fecha_digitacion' => 'datetime',
            'usuario_id' => 'integer',
        ];
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

    public function estaActivo(): bool
    {
        return (bool) $this->esta_activo;
    }
}
