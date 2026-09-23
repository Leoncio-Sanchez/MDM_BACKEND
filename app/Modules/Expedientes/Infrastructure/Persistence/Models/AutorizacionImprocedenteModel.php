<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $expediente_id
 * @property string $tipo_autorizacion
 * @property string|\DateTimeInterface $fecha_rechazo
 * @property string|null $documento
 * @property string|null $observaciones
 * @property int $usuario_id
 * @property string|\DateTimeInterface $fecha_digitacion
 */
class AutorizacionImprocedenteModel extends Model
{
    protected $table = 'autorizaciones_improcedentes';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'expediente_id',
        'tipo_autorizacion',
        'fecha_rechazo',
        'documento',
        'observaciones',
        'usuario_id',
        'fecha_digitacion',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_rechazo' => 'datetime',
            'fecha_digitacion' => 'datetime',
        ];
    }

    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:sP';
    }
}
