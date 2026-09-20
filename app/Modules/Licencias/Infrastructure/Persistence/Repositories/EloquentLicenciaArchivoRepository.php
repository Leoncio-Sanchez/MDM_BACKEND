<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;

use App\Modules\Licencias\Domain\Contracts\LicenciaArchivoRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class EloquentLicenciaArchivoRepository implements LicenciaArchivoRepositoryInterface
{
    public function obtenerRutas(int $licenciaId): array
    {
        return DB::table('licencias_funcionamiento_archivos')
            ->where('licencia_funcionamiento_id', $licenciaId)
            ->pluck('ruta_archivo')
            ->all();
    }

    public function eliminarRegistros(int $licenciaId): void
    {
        DB::table('licencias_funcionamiento_archivos')
            ->where('licencia_funcionamiento_id', $licenciaId)
            ->delete();
    }
}
