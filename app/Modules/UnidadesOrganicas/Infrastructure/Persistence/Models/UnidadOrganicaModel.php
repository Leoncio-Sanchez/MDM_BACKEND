<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string|null $sigla
 * @property bool $esta_activo
 */
final class UnidadOrganicaModel extends Model
{
    protected $table = 'unidades_organicas';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'sigla',
        'esta_activo',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
    ];
}
