<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenciaEstado extends Model
{
    protected $table = 'licencias_funcionamiento_estados';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_estado' => 'date',
            'fecha_digitacion' => 'datetime',
        ];
    }
}
