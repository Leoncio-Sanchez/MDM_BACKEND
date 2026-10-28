<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Infrastructure\Persistence\Repositories;

use App\Modules\Reportes\Domain\Contracts\ReportesRepositoryInterface;
use App\Modules\Reportes\Domain\ValueObjects\LicenciaPadron;
use Illuminate\Support\Facades\DB;

final readonly class QueryReportesRepository implements ReportesRepositoryInterface
{
    public function padronLicencias(string $desde, string $hasta): array
    {
        // Días hábiles con la función del Corte 2 (fn_diferencia_dias_habiles); la recepción
        // se pasa a fecha de Perú porque se guarda en UTC.
        $filas = DB::select(<<<'SQL'
            SELECT
                lf.numero_licencia,
                e.numero_expediente,
                lf.nombre_comercial,
                CASE WHEN p.tipo_persona = 'J' THEN p.nombres
                     ELSE TRIM(CONCAT_WS(' ', p.nombres, p.apellido_paterno, p.apellido_materno)) END AS titular,
                COALESCE((
                    SELECT pd.numero_documento
                    FROM personas_documentos pd
                    JOIN tipos_documento_identidad tdi ON tdi.id = pd.tipo_documento_identidad_id
                    WHERE pd.persona_id = p.id
                    ORDER BY (tdi.codigo = '06') DESC, (tdi.codigo = '01') DESC, pd.id
                    LIMIT 1
                ), '') AS documento_titular,
                COALESCE((
                    SELECT STRING_AGG(g.nombre, ', ' ORDER BY g.nombre)
                    FROM licencias_funcionamiento_giros lg
                    JOIN giros g ON g.id = lg.giro_id
                    WHERE lg.licencia_funcionamiento_id = lf.id
                ), '') AS giros,
                z.nombre AS zonificacion,
                nr.nombre AS nivel_riesgo,
                tl.nombre AS tipo_licencia,
                (e.fecha_recepcion AT TIME ZONE 'America/Lima')::DATE AS fecha_recepcion,
                lf.fecha_emision,
                fn_diferencia_dias_habiles((e.fecha_recepcion AT TIME ZONE 'America/Lima')::DATE, lf.fecha_emision) AS dias_habiles,
                tpt.plazo_atencion_dias
            FROM licencias_funcionamiento lf
            JOIN expedientes e ON e.id = lf.expediente_id
            JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
            JOIN personas p ON p.id = lf.titular_id
            JOIN zonificaciones z ON z.id = lf.zonificacion_id
            JOIN niveles_riesgo nr ON nr.id = lf.nivel_riesgo_id
            JOIN tipos_licencia tl ON tl.id = lf.tipo_licencia_id
            WHERE lf.fecha_emision BETWEEN ? AND ?
            ORDER BY lf.numero_licencia
            SQL, [$desde, $hasta]);

        return array_values(array_map(fn (\stdClass $f) => new LicenciaPadron(
            numeroLicencia: (int) $f->numero_licencia,
            numeroExpediente: (int) $f->numero_expediente,
            nombreComercial: (string) $f->nombre_comercial,
            titular: (string) $f->titular,
            documentoTitular: (string) $f->documento_titular,
            giros: (string) $f->giros,
            zonificacion: (string) $f->zonificacion,
            nivelRiesgo: (string) $f->nivel_riesgo,
            tipoLicencia: (string) $f->tipo_licencia,
            fechaRecepcion: (string) $f->fecha_recepcion,
            fechaEmision: (string) $f->fecha_emision,
            diasHabiles: (int) $f->dias_habiles,
            plazoDias: (int) $f->plazo_atencion_dias,
        ), $filas));
    }

    public function nivelesRiesgo(): array
    {
        return array_values(DB::table('niveles_riesgo')->orderBy('id')->pluck('nombre')->map(fn ($n) => (string) $n)->all());
    }
}
