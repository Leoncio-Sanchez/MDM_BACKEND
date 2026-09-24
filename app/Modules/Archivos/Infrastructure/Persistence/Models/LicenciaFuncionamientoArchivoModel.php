<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property string $nombre_original
 * @property string $nombre_archivo
 * @property string $ruta_archivo
 * @property string $extension
 * @property int $tamanio_bytes
 * @property \DateTimeInterface $fecha_digitacion
 * @property int $licencia_funcionamiento_id
 * @property int $usuario_id
 */
class LicenciaFuncionamientoArchivoModel extends Model
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

    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:sP';
    }
}
