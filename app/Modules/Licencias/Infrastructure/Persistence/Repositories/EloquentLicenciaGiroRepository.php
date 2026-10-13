<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;

use App\Modules\Licencias\Domain\Contracts\LicenciaGiroRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class EloquentLicenciaGiroRepository implements LicenciaGiroRepositoryInterface
{
    public function obtenerGiros(int $licenciaId): array
    {
        return DB::table('licencias_funcionamiento_giros')
            ->where('licencia_funcionamiento_id', $licenciaId)
            ->pluck('giro_id')
            ->all();
    }

    public function eliminarGiros(int $licenciaId): void
    {
        DB::table('licencias_funcionamiento_giros')
            ->where('licencia_funcionamiento_id', $licenciaId)
            ->delete();
    }

    public function insertarGiros(int $licenciaId, array $giroIds, int $usuarioId): void
    {
        if (empty($giroIds)) return;
        
        $rows = array_map(fn (int $giroId) => [
            'licencia_funcionamiento_id' => $licenciaId,
            'giro_id' => $giroId,
            'fecha_digitacion' => now(),
            'usuario_id' => $usuarioId, // obligatorio en el esquema real
        ], $giroIds);

        DB::table('licencias_funcionamiento_giros')->insert($rows);
    }
}
