<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ItseModel extends Model
{
    protected $table = 'itse';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'numero_itse',
        'fecha_expedicion',
        'fecha_solicitud_renovacion',
        'fecha_caducidad',
        'nombre_comercial',
        'direccion',
        'resolucion_numero',
        'area',
        'capacidad_aforo',
        'numero_recibo_pago',
        'observaciones',
        'se_puede_publicar',
        'fecha_notificacion',
        'fecha_digitacion',
        'tipo_itse_id',
        'expediente_id',
        'itse_principal_id',
        'usuario_id',
        'nivel_riesgo_id',
        'conductor_id',
        'titular_id',
    ];

    protected $casts = [
        'fecha_expedicion' => 'date',
        'fecha_solicitud_renovacion' => 'date',
        'fecha_caducidad' => 'date',
        'fecha_notificacion' => 'date',
        'fecha_digitacion' => 'datetime',
        'se_puede_publicar' => 'boolean',
        'area' => 'float',
    ];

    /** @return HasMany<ItseEstadoModel, $this> */
    public function estados(): HasMany
    {
        return $this->hasMany(ItseEstadoModel::class, 'itse_id');
    }
}
