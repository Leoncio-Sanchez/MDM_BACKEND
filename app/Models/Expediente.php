<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string|null $uuid
 * @property int $numero_expediente
 * @property Carbon|string $fecha_recepcion
 * @property string|null $observaciones
 * @property Carbon|string $fecha_vencimiento
 * @property Carbon|string $fecha_alerta
 * @property Carbon|string|null $fecha_suspension
 * @property int|null $dias_ampliacion
 * @property string|null $motivo_ampliacion
 * @property Carbon|string|null $fecha_digitacion_ampliacion
 * @property Carbon|string|null $fecha_digitacion
 * @property int $tipo_procedimiento_tupa_id
 * @property int $solicitante_id
 * @property int|null $representante_id
 * @property int|null $usuario_ampliacion_id
 * @property int|null $usuario_id
 * @property-read TipoProcedimientoTupa|null $tipoProcedimientoTupa
 * @property-read Usuario|null $usuario
 * @property-read Usuario|null $usuarioAmpliacion
 * @property-read Collection<int, AutorizacionImprocedente> $autorizacionesImprocedentes
 * @property-read LicenciaFuncionamiento|null $licencia
 * @property-read Itse|null $itse
 *
 * @method static Builder<static> activos()
 * @method static Builder<static> porVencer()
 */
class Expediente extends Model
{
    protected $table = 'expedientes';

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'numero_expediente',
        'fecha_recepcion',
        'observaciones',
        'fecha_vencimiento',
        'fecha_alerta',
        'fecha_suspension',
        'dias_ampliacion',
        'motivo_ampliacion',
        'fecha_digitacion_ampliacion',
        'fecha_digitacion',
        'tipo_procedimiento_tupa_id',
        'solicitante_id',
        'representante_id',
        'usuario_ampliacion',
        'usuario_ampliacion_id',
        'usuario_id',
    ];

    protected $casts = [
        'numero_expediente' => 'integer',
        'fecha_recepcion' => 'datetime',
        'fecha_vencimiento' => 'date',
        'fecha_alerta' => 'date',
        'fecha_suspension' => 'date',
        'fecha_digitacion' => 'datetime',
        'fecha_digitacion_ampliacion' => 'datetime',
        'dias_ampliacion' => 'integer',
        'tipo_procedimiento_tupa_id' => 'integer',
        'solicitante_id' => 'integer',
        'representante_id' => 'integer',
        'usuario_ampliacion' => 'integer',
        'usuario_ampliacion_id' => 'integer',
        'usuario_id' => 'integer',
    ];

    public function getUsuarioAmpliacionIdAttribute(): ?int
    {
        return isset($this->attributes['usuario_ampliacion']) ? (int) $this->attributes['usuario_ampliacion'] : null;
    }

    public function setUsuarioAmpliacionIdAttribute(?int $value): void
    {
        $this->attributes['usuario_ampliacion'] = $value;
    }

    /**
     * @return BelongsTo<TipoProcedimientoTupa, $this>
     */
    public function tipoProcedimientoTupa(): BelongsTo
    {
        return $this->belongsTo(TipoProcedimientoTupa::class, 'tipo_procedimiento_tupa_id');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuarioAmpliacion(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_ampliacion');
    }

    /**
     * @return HasMany<AutorizacionImprocedente, $this>
     */
    public function autorizacionesImprocedentes(): HasMany
    {
        return $this->hasMany(AutorizacionImprocedente::class, 'expediente_id');
    }

    /**
     * @return HasOne<LicenciaFuncionamiento, $this>
     */
    public function licencia(): HasOne
    {
        return $this->hasOne(LicenciaFuncionamiento::class, 'expediente_id');
    }

    /**
     * @return HasOne<Itse, $this>
     */
    public function itse(): HasOne
    {
        return $this->hasOne(Itse::class, 'expediente_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('fecha_vencimiento', '>=', now()->toDateString());
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePorVencer(Builder $query): Builder
    {
        return $query->where('fecha_alerta', '<=', now()->toDateString())
            ->where('fecha_vencimiento', '>=', now()->toDateString());
    }
}
