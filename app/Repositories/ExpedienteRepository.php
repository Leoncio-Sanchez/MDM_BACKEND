<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use stdClass;

final class ExpedienteRepository
{
    private const FILTROS_VALIDOS = [
        'ID',
        'NUMERO',
        'FECHA_RECEPCION',
        'FECHA_VENCIMIENTO',
        'NOMBRE_SOLICITANTE',
        'RUC_SOLICITANTE',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPendientes(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $sql = <<<'SQL'
SELECT
    e.id,
    e.numero_expediente,
    e.fecha_recepcion,
    tpt.nombre,
    e.fecha_vencimiento,
    e.fecha_alerta,
    TRIM(
        COALESCE(tsolicitante.apellido_paterno, '') || ' ' ||
        COALESCE(tsolicitante.apellido_materno, '') || ' ' ||
        COALESCE(tsolicitante.nombres, '')
    ) AS persona_nombre,
    texpedientes.licencia_pendiente,
    texpedientes.itse_pendiente,
    (e.fecha_alerta <= CURRENT_DATE) AS mostrar_alerta
FROM (
    SELECT
        e.id,
        CASE
            WHEN tpt.requiere_lf = 0 THEN 0
            WHEN tpt.requiere_lf = 1 AND lf.id IS NOT NULL THEN 0
            WHEN tpt.requiere_lf = 1 AND t_lf_improcedentes.id IS NOT NULL THEN 0
            ELSE 1
        END AS licencia_pendiente,
        CASE
            WHEN tpt.requiere_itse = 0 THEN 0
            WHEN tpt.requiere_itse = 1 AND i.id IS NOT NULL THEN 0
            WHEN tpt.requiere_itse = 1 AND t_itse_improcedentes.id IS NOT NULL THEN 0
            ELSE 1
        END AS itse_pendiente
    FROM expedientes e
    LEFT JOIN tipos_procedimiento_tupa tpt
        ON e.tipo_procedimiento_tupa_id = tpt.id
    LEFT JOIN licencias_funcionamiento lf
        ON e.id = lf.expediente_id
    LEFT JOIN itse i
        ON e.id = i.expediente_id
    LEFT JOIN (
        SELECT id, tipo_autorizacion, expediente_id
        FROM autorizaciones_improcedentes
        WHERE tipo_autorizacion = 'LF'
    ) AS t_lf_improcedentes
        ON e.id = t_lf_improcedentes.expediente_id
    LEFT JOIN (
        SELECT id, tipo_autorizacion, expediente_id
        FROM autorizaciones_improcedentes
        WHERE tipo_autorizacion = 'ITSE'
    ) AS t_itse_improcedentes
        ON e.id = t_itse_improcedentes.expediente_id
) AS texpedientes
INNER JOIN expedientes e
    ON texpedientes.id = e.id
LEFT JOIN tipos_procedimiento_tupa tpt
    ON e.tipo_procedimiento_tupa_id = tpt.id
LEFT JOIN personas tsolicitante
    ON e.solicitante_id = tsolicitante.id
WHERE texpedientes.licencia_pendiente = 1
   OR texpedientes.itse_pendiente = 1
ORDER BY e.fecha_alerta DESC
SQL;
        } else {
            $sql = <<<'SQL'
SELECT
    e.id,
    e.numero_expediente,
    e.fecha_recepcion,
    tpt.nombre,
    e.fecha_vencimiento,
    e.fecha_alerta,
    TRIM(
        COALESCE(tsolicitante.apellido_paterno, '') || ' ' ||
        COALESCE(tsolicitante.apellido_materno, '') || ' ' ||
        COALESCE(tsolicitante.nombres, '')
    ) AS persona_nombre,
    texpedientes.licencia_pendiente,
    texpedientes.itse_pendiente,
    e.fecha_alerta <= CURRENT_DATE AS mostrar_alerta
FROM (
    SELECT
        e.id,
        CASE
            WHEN tpt.requiere_lf = FALSE THEN FALSE
            WHEN tpt.requiere_lf = TRUE AND lf.id IS NOT NULL THEN FALSE
            WHEN tpt.requiere_lf = TRUE AND t_lf_improcedentes.id IS NOT NULL THEN FALSE
            ELSE TRUE
        END AS licencia_pendiente,
        CASE
            WHEN tpt.requiere_itse = FALSE THEN FALSE
            WHEN tpt.requiere_itse = TRUE AND i.id IS NOT NULL THEN FALSE
            WHEN tpt.requiere_itse = TRUE AND t_itse_improcedentes.id IS NOT NULL THEN FALSE
            ELSE TRUE
        END AS itse_pendiente
    FROM expedientes e
    LEFT JOIN tipos_procedimiento_tupa tpt
        ON e.tipo_procedimiento_tupa_id = tpt.id
    LEFT JOIN licencias_funcionamiento lf
        ON e.id = lf.expediente_id
    LEFT JOIN itse i
        ON e.id = i.expediente_id
    LEFT JOIN (
        SELECT id, tipo_autorizacion, expediente_id
        FROM autorizaciones_improcedentes
        WHERE tipo_autorizacion = 'LF'
    ) AS t_lf_improcedentes
        ON e.id = t_lf_improcedentes.expediente_id
    LEFT JOIN (
        SELECT id, tipo_autorizacion, expediente_id
        FROM autorizaciones_improcedentes
        WHERE tipo_autorizacion = 'ITSE'
    ) AS t_itse_improcedentes
        ON e.id = t_itse_improcedentes.expediente_id
) AS texpedientes
INNER JOIN expedientes e
    ON texpedientes.id = e.id
LEFT JOIN tipos_procedimiento_tupa tpt
    ON e.tipo_procedimiento_tupa_id = tpt.id
LEFT JOIN personas tsolicitante
    ON e.solicitante_id = tsolicitante.id
WHERE texpedientes.licencia_pendiente = TRUE
   OR texpedientes.itse_pendiente = TRUE
ORDER BY e.fecha_alerta DESC
SQL;
        }

        /** @var array<int, stdClass> $rows */
        $rows = DB::select($sql);

        return array_map(static fn (stdClass $row): array => (array) $row, $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscarPorFiltro(string $filtro, string $valor): array
    {
        $filtroUpper = strtoupper(trim($filtro));

        if (! in_array($filtroUpper, self::FILTROS_VALIDOS, true)) {
            throw new InvalidArgumentException(
                "Filtro '{$filtro}' no válido. Opciones: ".implode(', ', self::FILTROS_VALIDOS)
            );
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            [$whereClause, $param] = match ($filtroUpper) {
                'ID' => ['WHERE expedientes.id = ?', (int) $valor],
                'NUMERO' => ['WHERE expedientes.numero_expediente = ?', (int) $valor],
                'FECHA_RECEPCION' => ['WHERE date(expedientes.fecha_recepcion) = ?', (string) $valor],
                'FECHA_VENCIMIENTO' => ['WHERE expedientes.fecha_vencimiento = ?', (string) $valor],
                'NOMBRE_SOLICITANTE' => [
                    "WHERE TRIM(\n".
                    "    COALESCE(tsolicitante.apellido_paterno, '') || ' ' ||\n".
                    "    COALESCE(tsolicitante.apellido_materno, '') || ' ' ||\n".
                    "    COALESCE(tsolicitante.nombres, '')\n".
                    ') LIKE ?',
                    '%'.str_replace(' ', '%', $valor).'%',
                ],
                default => ['WHERE tsolicitante_ruc.numero_documento = ?', (string) $valor],
            };

            $sqlInterna = <<<SQL
    SELECT
        expedientes.id,
        expedientes.tipo_procedimiento_tupa_id,
        expedientes.numero_expediente,
        expedientes.fecha_recepcion,
        expedientes.solicitante_id,
        expedientes.representante_id,
        expedientes.observaciones,
        expedientes.fecha_vencimiento,
        expedientes.fecha_alerta,
        expedientes.fecha_suspension,
        expedientes.dias_ampliacion,
        expedientes.motivo_ampliacion,
        expedientes.usuario_ampliacion,
        expedientes.fecha_digitacion_ampliacion,
        expedientes.usuario_id,
        expedientes.fecha_digitacion,
        tpt.nombre                    AS nombre_procedimiento,
        tpt.plazo_atencion_dias,
        tpt.dias_alerta_vencimiento,
        tpt.requiere_itse,
        tpt.requiere_lf,
        TRIM(
            COALESCE(tsolicitante.apellido_paterno, '') || ' ' ||
            COALESCE(tsolicitante.apellido_materno, '') || ' ' ||
            COALESCE(tsolicitante.nombres, '')
        )                             AS solicitante_nombre,
        tsolicitante_ruc.numero_documento AS solicitante_ruc
    FROM expedientes
    LEFT JOIN tipos_procedimiento_tupa tpt
        ON expedientes.tipo_procedimiento_tupa_id = tpt.id
    LEFT JOIN personas AS tsolicitante
        ON expedientes.solicitante_id = tsolicitante.id
    LEFT JOIN (
        SELECT pd.id, pd.persona_id, pd.numero_documento
        FROM personas_documentos pd
        INNER JOIN tipos_documento_identidad tdi
            ON pd.tipo_documento_identidad_id = tdi.id
        WHERE tdi.codigo = '06'
    ) AS tsolicitante_ruc
        ON expedientes.solicitante_id = tsolicitante_ruc.persona_id
    {$whereClause}
SQL;

            $sqlFinal = <<<SQL
SELECT
    t.*,
    CASE
        WHEN t.requiere_lf = 0 THEN 0
        WHEN t.requiere_lf = 1 AND lf.id   IS NOT NULL THEN 0
        WHEN t.requiere_lf = 1 AND tlf.id  IS NOT NULL THEN 0
        ELSE 1
    END AS licencia_pendiente,
    CASE
        WHEN t.requiere_itse = 0 THEN 0
        WHEN t.requiere_itse = 1 AND i.id    IS NOT NULL THEN 0
        WHEN t.requiere_itse = 1 AND titse.id IS NOT NULL THEN 0
        ELSE 1
    END AS itse_pendiente,
    (t.fecha_alerta <= CURRENT_DATE) AS mostrar_alerta
FROM ({$sqlInterna}) AS t
LEFT JOIN licencias_funcionamiento lf
    ON t.id = lf.expediente_id
LEFT JOIN itse i
    ON t.id = i.expediente_id
LEFT JOIN (
    SELECT id, expediente_id
    FROM autorizaciones_improcedentes
    WHERE tipo_autorizacion = 'LF'
) AS tlf ON t.id = tlf.expediente_id
LEFT JOIN (
    SELECT id, expediente_id
    FROM autorizaciones_improcedentes
    WHERE tipo_autorizacion = 'ITSE'
) AS titse ON t.id = titse.expediente_id
ORDER BY t.fecha_recepcion DESC
SQL;
        } else {
            [$whereClause, $param] = match ($filtroUpper) {
                'ID' => ['WHERE expedientes.id = ?', (int) $valor],
                'NUMERO' => ['WHERE expedientes.numero_expediente = ?', (int) $valor],
                'FECHA_RECEPCION' => ['WHERE expedientes.fecha_recepcion::date = ?', (string) $valor],
                'FECHA_VENCIMIENTO' => ['WHERE expedientes.fecha_vencimiento = ?', (string) $valor],
                'NOMBRE_SOLICITANTE' => [
                    "WHERE TRIM(\n".
                    "    COALESCE(tsolicitante.apellido_paterno, '') || ' ' ||\n".
                    "    COALESCE(tsolicitante.apellido_materno, '') || ' ' ||\n".
                    "    COALESCE(tsolicitante.nombres, '')\n".
                    ') ILIKE ?',
                    '%'.str_replace(' ', '%', $valor).'%',
                ],
                default => ['WHERE tsolicitante_ruc.numero_documento = ?', (string) $valor],
            };

            $sqlInterna = <<<SQL
    SELECT
        expedientes.id,
        expedientes.tipo_procedimiento_tupa_id,
        expedientes.numero_expediente,
        expedientes.fecha_recepcion,
        expedientes.solicitante_id,
        expedientes.representante_id,
        expedientes.observaciones,
        expedientes.fecha_vencimiento,
        expedientes.fecha_alerta,
        expedientes.fecha_suspension,
        expedientes.dias_ampliacion,
        expedientes.motivo_ampliacion,
        expedientes.usuario_ampliacion,
        expedientes.fecha_digitacion_ampliacion,
        expedientes.usuario_id,
        expedientes.fecha_digitacion,
        tpt.nombre                    AS nombre_procedimiento,
        tpt.plazo_atencion_dias,
        tpt.dias_alerta_vencimiento,
        tpt.requiere_itse,
        tpt.requiere_lf,
        TRIM(
            COALESCE(tsolicitante.apellido_paterno, '') || ' ' ||
            COALESCE(tsolicitante.apellido_materno, '') || ' ' ||
            COALESCE(tsolicitante.nombres, '')
        )                             AS solicitante_nombre,
        tsolicitante_ruc.numero_documento AS solicitante_ruc
    FROM expedientes
    LEFT JOIN tipos_procedimiento_tupa tpt
        ON expedientes.tipo_procedimiento_tupa_id = tpt.id
    LEFT JOIN personas AS tsolicitante
        ON expedientes.solicitante_id = tsolicitante.id
    LEFT JOIN (
        SELECT pd.id, pd.persona_id, pd.numero_documento
        FROM personas_documentos pd
        INNER JOIN tipos_documento_identidad tdi
            ON pd.tipo_documento_identidad_id = tdi.id
        WHERE tdi.codigo = '06'
    ) AS tsolicitante_ruc
        ON expedientes.solicitante_id = tsolicitante_ruc.persona_id
    {$whereClause}
SQL;

            $sqlFinal = <<<SQL
SELECT
    t.*,
    CASE
        WHEN t.requiere_lf = FALSE THEN FALSE
        WHEN t.requiere_lf = TRUE AND lf.id   IS NOT NULL THEN FALSE
        WHEN t.requiere_lf = TRUE AND tlf.id  IS NOT NULL THEN FALSE
        ELSE TRUE
    END AS licencia_pendiente,
    CASE
        WHEN t.requiere_itse = FALSE THEN FALSE
        WHEN t.requiere_itse = TRUE AND i.id    IS NOT NULL THEN FALSE
        WHEN t.requiere_itse = TRUE AND titse.id IS NOT NULL THEN FALSE
        ELSE TRUE
    END AS itse_pendiente,
    t.fecha_alerta <= CURRENT_DATE AS mostrar_alerta
FROM ({$sqlInterna}) AS t
LEFT JOIN licencias_funcionamiento lf
    ON t.id = lf.expediente_id
LEFT JOIN itse i
    ON t.id = i.expediente_id
LEFT JOIN (
    SELECT id, expediente_id
    FROM autorizaciones_improcedentes
    WHERE tipo_autorizacion = 'LF'
) AS tlf ON t.id = tlf.expediente_id
LEFT JOIN (
    SELECT id, expediente_id
    FROM autorizaciones_improcedentes
    WHERE tipo_autorizacion = 'ITSE'
) AS titse ON t.id = titse.expediente_id
ORDER BY t.fecha_recepcion DESC
SQL;
        }

        /** @var array<int, stdClass> $rows */
        $rows = DB::select($sqlFinal, [$param]);

        return array_map(static fn (stdClass $row): array => (array) $row, $rows);
    }
}
