<?php
declare(strict_types=1);

namespace App\Modules\Personas\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
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
 * @property \Carbon\CarbonImmutable $fecha_creacion
 * @property \Carbon\CarbonImmutable $fecha_actualizacion
 * @property int $user_id
 * @property \Illuminate\Database\Eloquent\Collection<int, PersonaDocumentoModel> $documentos
 */
class PersonaModel extends Model
{
    protected $table = 'personas';
    public $timestamps = false;

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

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    /** @return HasMany<PersonaDocumentoModel, $this> */
    public function documentos(): HasMany
    {
        return $this->hasMany(PersonaDocumentoModel::class, 'persona_id', 'id');
    }
}
