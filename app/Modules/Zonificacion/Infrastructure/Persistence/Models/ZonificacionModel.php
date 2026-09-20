<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ZonificacionModel extends Model
{
    protected $table = 'zonificaciones';

    public $timestamps = false;

    protected $fillable = ['codigo', 'nombre', 'esta_activo', 'fecha_digitacion', 'usuario_id'];

    protected $casts = [
        'esta_activo' => 'boolean',
        'fecha_digitacion' => 'datetime',
        'usuario_id' => 'integer',
    ];
}
