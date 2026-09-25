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
 * @property int $giro_id
 * @property int|null $usuario_id
 * @property Carbon|string|null $fecha_digitacion
 *
 * @method static Builder<self> query()
 */
class ItseGiro extends Model
{
    protected $table = 'itse_giros';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_digitacion' => 'datetime',
            'giro_id' => 'integer',
            'itse_id' => 'integer',
        ];
    }

    /** @return BelongsTo<Itse, $this> */
    public function itse(): BelongsTo
    {
        return $this->belongsTo(Itse::class, 'itse_id');
    }

    /** @return BelongsTo<Giro, $this> */
    public function giro(): BelongsTo
    {
        return $this->belongsTo(Giro::class, 'giro_id');
    }
}
