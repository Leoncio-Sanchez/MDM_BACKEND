<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $expediente_id
 * @property string $motivo
 * @property string|null $documento
 * @property string|\DateTimeInterface $fecha_anulacion
 * @property int $usuario_id
 * @property User|null $usuario
 */
class ExpedienteAnulacionModel extends Model
{
    protected $table = 'expedientes_anulaciones';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['expediente_id', 'motivo', 'documento', 'fecha_anulacion', 'usuario_id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['fecha_anulacion' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:sP';
    }
}
