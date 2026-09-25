<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $itse_id
 * @property int $estado_id
 * @property Carbon|string|null $fecha_estado
 * @property string $documento
 * @property string $observaciones
 * @property int|null $usuario_id
 * @property Carbon|string|null $fecha_digitacion
 *
 * @method static Builder<self> query()
 */
class ItseEstado extends Model
{
    protected $table = 'itse_estados';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_estado' => 'date',
            'fecha_digitacion' => 'datetime',
            'estado_id' => 'integer',
            'itse_id' => 'integer',
        ];
    }

    /** @return BelongsTo<Itse, $this> */
    public function itse(): BelongsTo
    {
        return $this->belongsTo(Itse::class, 'itse_id');
    }

    /** @return BelongsTo<Estado, $this> */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}
