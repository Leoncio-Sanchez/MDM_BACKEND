<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $expedientes
 * @property bool $licencias
 * @property bool $itse
 * @property bool $admin
 * @property bool $es_inspector
 * @property bool $es_jefe_itse
 * @property bool $reportes_completos
 * @property int $user_id_digitador
 * @property string $fecha_digitacion
 */
final class UsuarioPerfilModel extends Model
{
    protected $table = 'usuarios_perfiles';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'expedientes',
        'licencias',
        'itse',
        'admin',
        'es_inspector',
        'es_jefe_itse',
        'reportes_completos',
        'user_id_digitador',
        'fecha_digitacion',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'expedientes' => 'boolean',
            'licencias' => 'boolean',
            'itse' => 'boolean',
            'admin' => 'boolean',
            'es_inspector' => 'boolean',
            'es_jefe_itse' => 'boolean',
            'reportes_completos' => 'boolean',
            'user_id_digitador' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<UsuarioModel, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'user_id');
    }
}
