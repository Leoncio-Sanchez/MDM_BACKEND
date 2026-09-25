<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $dia
 * @property int $mes
 * @property string|null $descripcion
 */
class FeriadoRecurrente extends Model
{
    protected $table = 'feriados_recurrentes';

    public $timestamps = false;

    protected $fillable = [
        'dia',
        'mes',
        'descripcion',
    ];

    protected $casts = [
        'dia' => 'integer',
        'mes' => 'integer',
    ];
}
