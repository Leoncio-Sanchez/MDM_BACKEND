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
        'estado',
        'fecha',
        'observaciones',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];
}
