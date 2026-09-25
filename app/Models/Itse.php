<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property int $numero_itse
 * @property Carbon|string|null $fecha_expedicion
 * @property Carbon|string|null $fecha_solicitud_renovacion
 * @property Carbon|string|null $fecha_caducidad
 * @property string $nombre_comercial
 * @property string $direccion
 * @property string $resolucion_numero
 * @property float|string $area
 * @property int $capacidad_aforo
 * @property string $numero_recibo_pago
 * @property string|null $observaciones
 * @property bool $se_puede_publicar
 * @property Carbon|string|null $fecha_notificacion
 * @property Carbon|string|null $fecha_digitacion
 * @property int $tipo_itse_id
 * @property int $expediente_id
 * @property int|null $itse_principal_id
 * @property int|null $usuario_id
 * @property int $nivel_riesgo_id
 * @property int $conductor_id
 * @property int $titular_id
 *
 * @method static Builder<self> query()
 */
class Itse extends Model
{
    protected $table = 'itse';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_expedicion' => 'date',
            'fecha_solicitud_renovacion' => 'date',
            'fecha_caducidad' => 'date',
            'fecha_notificacion' => 'date',
            'fecha_digitacion' => 'datetime',
            'se_puede_publicar' => 'boolean',
            'area' => 'decimal:2',
            'capacidad_aforo' => 'integer',
            'tipo_itse_id' => 'integer',
        ];
    }

    /** @return HasMany<ItseEstado, $this> */
    public function estados(): HasMany
    {
        return $this->hasMany(ItseEstado::class, 'itse_id');
    }

    /** @return HasMany<ItseGiro, $this> */
    public function giros(): HasMany
    {
        return $this->hasMany(ItseGiro::class, 'itse_id');
    }

    /** @return BelongsTo<self, $this> */
    public function itsePrincipal(): BelongsTo
    {
        return $this->belongsTo(self::class, 'itse_principal_id');
    }

    /** @return HasMany<self, $this> */
    public function itseDependientes(): HasMany
    {
        return $this->hasMany(self::class, 'itse_principal_id');
    }
}
