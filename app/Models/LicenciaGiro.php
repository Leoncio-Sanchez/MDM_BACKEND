<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenciaGiro extends Model
{
    protected $table = 'licencias_funcionamiento_giros';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_digitacion' => 'datetime',
        ];
    }
}
