<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Infrastructure\Persistence\Repositories;

use App\Modules\Dashboard\Domain\Contracts\ResumenRepositoryInterface;
use App\Modules\Dashboard\Domain\ValueObjects\ResumenGeneral;
use DateTimeImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Una consulta por módulo, con COUNT(*) FILTER para sacar varias cifras de una pasada. */
final readonly class QueryResumenRepository implements ResumenRepositoryInterface
{
    /** Licencia con un estado cuyo catálogo no está activo (clausura, revocación…). */
    private const LICENCIA_INACTIVA = 'EXISTS (SELECT 1 FROM licencias_funcionamiento_estados te
        JOIN estados est ON est.id = te.estado_id
        WHERE te.licencia_funcionamiento_id = lf.id AND est.esta_activo = FALSE)';

    private const ITSE_INACTIVO = 'EXISTS (SELECT 1 FROM itse_estados te
        JOIN estados est ON est.id = te.estado_id
        WHERE te.itse_id = i.id AND est.esta_activo = FALSE)';

    /** Ya tiene una renovación que no fue inactivada: el certificado anterior quedó reemplazado. */
    private const ITSE_RENOVADO = 'EXISTS (SELECT 1 FROM itse r
        WHERE r.itse_principal_id = i.id
          AND NOT EXISTS (SELECT 1 FROM itse_estados te JOIN estados est ON est.id = te.estado_id
                          WHERE te.itse_id = r.id AND est.esta_activo = FALSE))';

    public function resumen(DateTimeImmutable $hoy, int $diasAlertaItse): ResumenGeneral
    {
        $dia = $hoy->format('Y-m-d');
        $inicioMes = $hoy->modify('first day of this month')->format('Y-m-d');
        $finMes = $hoy->modify('last day of this month')->format('Y-m-d');
        $limiteItse = $hoy->modify("+{$diasAlertaItse} days")->format('Y-m-d');

        $expedientes = DB::query()
            ->fromSub($this->expedientesPendientes(), 'p')
            ->selectRaw('COUNT(*) AS en_tramite')
            ->selectRaw('COUNT(*) FILTER (WHERE p.fecha_alerta <= ? AND p.fecha_vencimiento >= ?) AS por_vencer', [$dia, $dia])
            ->selectRaw('COUNT(*) FILTER (WHERE p.fecha_vencimiento < ?) AS vencidos', [$dia])
            ->first();

        $licencias = DB::table('licencias_funcionamiento as lf')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(*) FILTER (WHERE NOT '.self::LICENCIA_INACTIVA.'
                AND (lf.es_vigencia_indeterminada OR lf.fecha_fin_vigencia IS NULL OR lf.fecha_fin_vigencia >= ?)) AS vigentes', [$dia])
            ->selectRaw('COUNT(*) FILTER (WHERE lf.fecha_emision BETWEEN ? AND ?) AS emitidas_mes', [$inicioMes, $finMes])
            ->first();

        $itse = DB::table('itse as i')
            ->selectRaw('COUNT(*) FILTER (WHERE NOT '.self::ITSE_INACTIVO.' AND NOT '.self::ITSE_RENOVADO.'
                AND i.fecha_caducidad >= ?) AS vigentes', [$dia])
            ->selectRaw('COUNT(*) FILTER (WHERE NOT '.self::ITSE_INACTIVO.' AND NOT '.self::ITSE_RENOVADO.'
                AND i.fecha_caducidad BETWEEN ? AND ?) AS por_vencer', [$dia, $limiteItse])
            ->selectRaw('COUNT(*) FILTER (WHERE i.fecha_expedicion BETWEEN ? AND ?) AS emitidos_mes', [$inicioMes, $finMes])
            ->first();

        return new ResumenGeneral(
            expedientesEnTramite: (int) ($expedientes->en_tramite ?? 0),
            expedientesPorVencer: (int) ($expedientes->por_vencer ?? 0),
            expedientesVencidos: (int) ($expedientes->vencidos ?? 0),
            licenciasVigentes: (int) ($licencias->vigentes ?? 0),
            licenciasEmitidasMes: (int) ($licencias->emitidas_mes ?? 0),
            licenciasTotal: (int) ($licencias->total ?? 0),
            itseVigentes: (int) ($itse->vigentes ?? 0),
            itsePorVencer: (int) ($itse->por_vencer ?? 0),
            itseEmitidosMes: (int) ($itse->emitidos_mes ?? 0),
            diasAlertaItse: $diasAlertaItse,
        );
    }

    /**
     * Misma regla que "expedientes pendientes": cada autorización que el procedimiento
     * requiere (LF / ITSE) sigue pendiente hasta emitirse o declararse improcedente.
     */
    private function expedientesPendientes(): Builder
    {
        $pendiente = fn (string $requiere, string $tabla, string $tipo) => fn (Builder $q) => $q
            ->where("tpt.{$requiere}", true)
            ->whereNotExists(fn (Builder $s) => $s->selectRaw('1')->from($tabla)->whereColumn("{$tabla}.expediente_id", 'e.id'))
            ->whereNotExists(fn (Builder $s) => $s->selectRaw('1')->from('autorizaciones_improcedentes as ai')
                ->whereColumn('ai.expediente_id', 'e.id')->where('ai.tipo_autorizacion', $tipo));

        return DB::table('expedientes as e')
            ->join('tipos_procedimiento_tupa as tpt', 'tpt.id', '=', 'e.tipo_procedimiento_tupa_id')
            ->where(fn (Builder $q) => $q
                ->where($pendiente('requiere_lf', 'licencias_funcionamiento', 'LF'))
                ->orWhere($pendiente('requiere_itse', 'itse', 'ITSE')))
            ->select(['e.fecha_alerta', 'e.fecha_vencimiento']);
    }
}
