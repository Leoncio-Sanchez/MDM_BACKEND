<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class ItseEstadoModel extends Model
{
    protected $table = 'itse_estados';
    public $timestamps = false;

    protected $fillable = [
        'itse_id',
        'estado_id',
        'fecha_estado',
        'documento',
        'observaciones',
        'usuario_id',
        'fecha_digitacion',
    ];

    protected $casts = [
        'fecha_estado' => 'date',
        'fecha_digitacion' => 'datetime',
    ];
}
