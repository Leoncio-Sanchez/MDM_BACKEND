<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class NivelRiesgoModel extends Model
{
    protected $table = 'niveles_riesgo';

    public $timestamps = false;

    protected $fillable = ['codigo', 'nombre', 'esta_activo'];

    protected $casts = ['esta_activo' => 'boolean'];
}
