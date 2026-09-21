<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\ItseGiroRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class EloquentItseGiroRepository implements ItseGiroRepositoryInterface
{
    /**
     * Replica exacta de _SQL_GIROS_POR_ITSE en services/giro.py de Django.
     * Columnas: id, itse_id, giro_id, ciiu_id, nombre
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPorItse(int $itseId): array
    {
        $sql = <<<'SQL'
SELECT
    ig.id,
    ig.itse_id,
    ig.giro_id,
    g.ciiu_id,
    g.nombre
FROM itse_giros ig
INNER JOIN giros g ON g.id = ig.giro_id
WHERE ig.itse_id = ?
ORDER BY g.nombre
SQL;

        $results = DB::select($sql, [$itseId]);

        return array_map(fn ($row) => (array) $row, $results);
    }
}
