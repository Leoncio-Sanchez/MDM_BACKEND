<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $expedientes
 * @property bool $licencias
 * @property bool $itse
 * @property bool $admin
 * @property int|null $user_id_digitador
 * @property Carbon|null $fecha_digitacion
 * @property-read Usuario $usuario
 *
 * @method static UsuarioPerfil create(array<string, mixed> $attributes = [])
 * @method static UsuarioPerfil findOrFail(mixed $id, array<string> $columns = ['*'])
 */
class UsuarioPerfil extends Model
{
    protected $table = 'usuarios_perfiles';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'expedientes',
        'licencias',
        'itse',
        'admin',
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
            'user_id_digitador' => 'integer',
            'fecha_digitacion' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
