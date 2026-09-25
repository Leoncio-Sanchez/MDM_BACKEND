<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $tipo_persona
 * @property string $sexo
 * @property string|null $apellido_paterno
 * @property string|null $apellido_materno
 * @property string $nombres
 * @property string $direccion
 * @property string $distrito
 * @property string $provincia
 * @property string $departamento
 * @property string|null $telefono
 * @property string|null $correo_electronico
 * @property Carbon|string|null $fecha_creacion
 * @property Carbon|string|null $fecha_actualizacion
 * @property int $user_id
 * @property-read Collection<int, PersonaDocumento> $documentos
 * @property-read Usuario|null $user
 *
 * @method static Builder<static> query()
 */
class Persona extends Model
{
    protected $table = 'personas';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_persona',
        'sexo',
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'direccion',
        'distrito',
        'provincia',
        'departamento',
        'telefono',
        'correo_electronico',
        'fecha_creacion',
        'fecha_actualizacion',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
            'user_id' => 'integer',
        ];
    }

    /**
     * @return HasMany<PersonaDocumento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(PersonaDocumento::class, 'persona_id', 'id');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id', 'id');
    }
}
