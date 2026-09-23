<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property int $numero_expediente
 * @property string|\DateTimeInterface $fecha_recepcion
 * @property int $tipo_procedimiento_tupa_id
 * @property int $solicitante_id
 * @property int|null $representante_id
 * @property string|null $observaciones
 * @property string|\DateTimeInterface $fecha_vencimiento
 * @property string|\DateTimeInterface $fecha_alerta
 * @property string|\DateTimeInterface|null $fecha_suspension
 * @property int|null $dias_ampliacion
 * @property string|null $motivo_ampliacion
 * @property int|null $usuario_ampliacion
 * @property string|\DateTimeInterface|null $fecha_digitacion_ampliacion
 * @property int $usuario_id
 * @property string|\DateTimeInterface $fecha_digitacion
 */
class ExpedienteModel extends Model
{
    protected $table = 'expedientes';
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'uuid',
        'numero_expediente',
        'fecha_recepcion',
        'tipo_procedimiento_tupa_id',
        'solicitante_id',
        'representante_id',
        'observaciones',
        'fecha_vencimiento',
        'fecha_alerta',
        'fecha_suspension',
        'dias_ampliacion',
        'motivo_ampliacion',
        'usuario_ampliacion',
        'fecha_digitacion_ampliacion',
        'usuario_id',
        'fecha_digitacion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_recepcion' => 'datetime',
            'fecha_vencimiento' => 'date',
            'fecha_alerta' => 'date',
            'fecha_suspension' => 'date',
            'fecha_digitacion_ampliacion' => 'datetime',
            'fecha_digitacion' => 'datetime',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<AutorizacionImprocedenteModel, $this>
     */
    public function autorizacionesImprocedentes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AutorizacionImprocedenteModel::class, 'expediente_id');
    }

    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:sP';
    }
}
