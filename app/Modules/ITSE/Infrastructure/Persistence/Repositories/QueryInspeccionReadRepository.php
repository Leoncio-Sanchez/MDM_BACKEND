<?php

declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\InspeccionReadRepositoryInterface;
use App\Modules\ITSE\Domain\ValueObjects\SituacionInspeccion;
use Illuminate\Support\Facades\DB;

final readonly class QueryInspeccionReadRepository implements InspeccionReadRepositoryInterface
{
    public function situacion(int $expedienteId): ?SituacionInspeccion
    {
        $desde = config('inspecciones.acta_obligatoria_desde');

        $fila = DB::selectOne(<<<'SQL'
            SELECT
                e.numero_expediente,
                tpt.requiere_itse AND (CAST(? AS DATE) IS NULL OR e.fecha_recepcion >= CAST(? AS DATE)) AS exige_acta,
                ua.id AS acta_id, ua.numero_acta, ua.resultado, ua.fecha_visita, ua.area_m2, ua.aforo
            FROM expedientes e
            JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
            LEFT JOIN LATERAL (
                SELECT id, numero_acta, resultado, fecha_visita, area_m2, aforo
                FROM inspecciones_actas
                WHERE expediente_id = e.id AND fecha_anulacion IS NULL
                ORDER BY visita DESC LIMIT 1
            ) ua ON TRUE
            WHERE e.id = ?
            SQL, [$desde, $desde, $expedienteId]);

        if ($fila === null) {
            return null;
        }

        return new SituacionInspeccion(
            numeroExpediente: (int) $fila->numero_expediente,
            exigeActa: (bool) $fila->exige_acta,
            ultimaActaId: $fila->acta_id === null ? null : (int) $fila->acta_id,
            ultimaActaNumero: $fila->numero_acta === null ? null : (string) $fila->numero_acta,
            ultimaActaResultado: $fila->resultado === null ? null : (string) $fila->resultado,
            fechaVisita: $fila->fecha_visita === null ? null : (string) $fila->fecha_visita,
            areaM2: $fila->area_m2 === null ? null : (float) $fila->area_m2,
            aforo: $fila->aforo === null ? null : (int) $fila->aforo,
        );
    }
}
