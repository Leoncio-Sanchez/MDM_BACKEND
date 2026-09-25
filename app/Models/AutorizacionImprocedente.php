<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $expediente_id
 * @property string $tipo_autorizacion
 * @property Carbon|string $fecha_rechazo
 * @property string $documento
 * @property string $observaciones
 * @property int|null $usuario_id
 * @property Carbon|string|null $fecha_digitacion
 * @property-read Expediente|null $expediente
 * @property-read Usuario|null $usuario
 */
class AutorizacionImprocedente extends Model
{
    protected $table = 'autorizaciones_improcedentes';

    public $timestamps = false;

    protected $fillable = [
        'expediente_id',
        'tipo_autorizacion',
        'fecha_rechazo',
        'documento',
        'observaciones',
        'usuario_id',
        'fecha_digitacion',
    ];

    protected $casts = [
        'expediente_id' => 'integer',
        'fecha_rechazo' => 'date',
        'usuario_id' => 'integer',
        'fecha_digitacion' => 'datetime',
    ];

    /**
     * @return BelongsTo<Expediente, $this>
     */
    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
