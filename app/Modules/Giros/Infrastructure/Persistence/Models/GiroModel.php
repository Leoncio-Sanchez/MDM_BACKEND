<?php

declare(strict_types=1);

namespace App\Modules\Giros\Infrastructure\Persistence\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $ciiu_id
 * @property string $nombre
 * @property bool $esta_activo
 * @property Carbon|null $fecha_digitacion
 * @property int $usuario_id
 */
class GiroModel extends Model
{
    protected $table = 'giros';

    public $timestamps = false;

    protected $fillable = ['ciiu_id', 'nombre', 'esta_activo', 'fecha_digitacion', 'usuario_id'];

    protected $casts = [
        'esta_activo' => 'boolean',
        'fecha_digitacion' => 'datetime',
    ];
}
