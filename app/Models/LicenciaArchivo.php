<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property string $nombre_original
 * @property string $nombre_archivo
 * @property string $ruta_archivo
 * @property string $extension
 * @property int $tamanio_bytes
 * @property Carbon|string $fecha_digitacion
 * @property int $licencia_funcionamiento_id
 * @property int $usuario_id
 * @property-read LicenciaFuncionamiento|null $licenciaFuncionamiento
 * @property-read Usuario|null $usuario
 *
 * @method static Builder<static> query()
 */
class LicenciaArchivo extends Model
{
    protected $table = 'licencias_funcionamiento_archivos';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'uuid',
        'nombre_original',
        'nombre_archivo',
        'ruta_archivo',
        'extension',
        'tamanio_bytes',
        'fecha_digitacion',
        'licencia_funcionamiento_id',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tamanio_bytes' => 'integer',
            'licencia_funcionamiento_id' => 'integer',
            'usuario_id' => 'integer',
            'fecha_digitacion' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<LicenciaFuncionamiento, $this>
     */
    public function licenciaFuncionamiento(): BelongsTo
    {
        return $this->belongsTo(LicenciaFuncionamiento::class, 'licencia_funcionamiento_id');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
