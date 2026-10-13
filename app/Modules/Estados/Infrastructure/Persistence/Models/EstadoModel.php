<?php
declare(strict_types=1);

namespace App\Modules\Estados\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\App\Modules\Estados\Infrastructure\Persistence\Models\EstadoModelFactory;

/**
 * @method static \Database\Factories\App\Modules\Estados\Infrastructure\Persistence\Models\EstadoModelFactory factory($count = null, $state = [])
 */
/**
 * Columnas según database/sql/esquema_produccion.sql (Larastan no puede leerlas del SQL).
 *
 * @property int $id
 * @property string $nombre
 * @property bool $es_para_lf
 * @property bool $es_para_itse
 * @property bool $esta_activo
 */
final class EstadoModel extends Model
{
    /** @use HasFactory<\Database\Factories\App\Modules\Estados\Infrastructure\Persistence\Models\EstadoModelFactory> */
    use HasFactory;

    protected $table = 'estados';
    public $timestamps = false;

    protected $fillable = ['nombre', 'es_para_lf', 'es_para_itse', 'esta_activo'];

    protected function casts(): array
    {
        return [
            'es_para_lf' => 'boolean',
            'es_para_itse' => 'boolean',
            'esta_activo' => 'boolean',
        ];
    }

    protected static function newFactory(): EstadoModelFactory
    {
        return EstadoModelFactory::new();
    }
}
