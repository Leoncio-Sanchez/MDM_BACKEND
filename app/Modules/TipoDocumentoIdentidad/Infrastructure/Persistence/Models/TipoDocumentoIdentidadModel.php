<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $esta_activo
 * @property bool $es_para_natural
 * @property bool $es_para_juridica
 */
class TipoDocumentoIdentidadModel extends Model
{
    protected $table = 'tipos_documento_identidad';

    public $timestamps = false;

    protected $fillable = ['codigo', 'nombre', 'esta_activo', 'es_para_natural', 'es_para_juridica'];

    protected $casts = [
        'esta_activo' => 'boolean',
        'es_para_natural' => 'boolean',
        'es_para_juridica' => 'boolean',
    ];
}
