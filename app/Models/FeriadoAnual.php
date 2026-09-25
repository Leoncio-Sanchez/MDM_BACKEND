<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $feriado
 * @property string|null $descripcion
 */
class FeriadoAnual extends Model
{
    protected $table = 'feriados_anuales';

    public $timestamps = false;

    protected $fillable = [
        'feriado',
        'descripcion',
    ];

    protected $casts = [
        'feriado' => 'date',
    ];
}
