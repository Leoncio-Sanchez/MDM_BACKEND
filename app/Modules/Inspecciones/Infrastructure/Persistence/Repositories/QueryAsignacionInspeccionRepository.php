<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Infrastructure\Persistence\Repositories;

use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionNoEncontrada;
use App\Modules\Inspecciones\Domain\Exceptions\ExpedienteYaAsignado;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosAsignacion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final readonly class QueryAsignacionInspeccionRepository implements AsignacionInspeccionRepositoryInterface
{
    /** Expediente "e" que espera inspección ITSE */
    private const ESPERA_INSPECCION = <<<'SQL'
        tpt.requiere_itse = TRUE
        AND NOT EXISTS (SELECT 1 FROM itse i WHERE i.expediente_id = e.id)
        AND NOT EXISTS (SELECT 1 FROM autorizaciones_improcedentes ai WHERE ai.expediente_id = e.id AND ai.tipo_autorizacion = 'ITSE')
        AND NOT EXISTS (SELECT 1 FROM expedientes_anulaciones ea WHERE ea.expediente_id = e.id)
        SQL;

    /** Sin acta vigente Aprobada ni Desaprobada: todavía hay que visitar el local */
    private const SIN_DICTAMEN_FINAL = <<<'SQL'
        NOT EXISTS (
            SELECT 1 FROM inspecciones_actas acf
            WHERE acf.expediente_id = e.id AND acf.fecha_anulacion IS NULL AND acf.resultado IN ('APROBADO', 'DESAPROBADO')
        )
        SQL;

    /**
     * Última acta vigente del expediente "e" (alias "uact") y, si después se anuló la que
     * seguía, esa acta anulada (alias "anu"): el inspector debe volver a registrar esa visita.
     */
    private const ULTIMA_ACTA = <<<'SQL'
        LEFT JOIN LATERAL (
            SELECT id, numero_acta, visita, resultado, fecha_limite_subsanacion, observaciones
            FROM inspecciones_actas WHERE expediente_id = e.id AND fecha_anulacion IS NULL ORDER BY visita DESC LIMIT 1
        ) uact ON TRUE
        LEFT JOIN LATERAL (
            SELECT numero_acta, motivo_anulacion, fecha_anulacion
            FROM inspecciones_actas
            WHERE expediente_id = e.id AND fecha_anulacion IS NOT NULL AND visita > COALESCE(uact.visita, 0)
            ORDER BY fecha_anulacion DESC LIMIT 1
        ) anu ON TRUE
        SQL;

    private const CAMPOS_ULTIMA_ACTA = <<<'SQL'
        uact.id AS ultima_acta_id,
        uact.numero_acta AS ultima_acta_numero,
        uact.visita AS ultima_acta_visita,
        uact.resultado AS ultima_acta_resultado,
        uact.fecha_limite_subsanacion,
        uact.observaciones AS ultima_acta_observaciones,
        anu.numero_acta AS acta_anulada_numero,
        anu.motivo_anulacion AS acta_anulada_motivo,
        anu.fecha_anulacion AS acta_anulada_fecha
        SQL;

    private const NOMBRE_SOLICITANTE = "TRIM(CONCAT_WS(' ', p.apellido_paterno, p.apellido_materno, p.nombres))";

    private static function nombreUsuario(string $alias): string
    {
        return "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', {$alias}.first_name, {$alias}.last_name)), ''), {$alias}.username)";
    }

    /** Acompañantes vigentes de la asignación "a", como arreglo JSON (vacío si no hay asignación) */
    private static function acompanantes(): string
    {
        $nombre = self::nombreUsuario('ux');

        return <<<SQL
            COALESCE((
                SELECT json_agg(json_build_object('id', ux.id, 'nombre', {$nombre}, 'activo', ux.is_active) ORDER BY aa.id)
                FROM inspecciones_asignaciones_acompanantes aa
                JOIN auth_user ux ON ux.id = aa.inspector_id
                WHERE aa.asignacion_id = a.id AND aa.fecha_retiro IS NULL
            ), '[]') AS acompanantes
            SQL;
    }

    public function expedientesPorInspeccionar(): array
    {
        $solicitante = self::NOMBRE_SOLICITANTE;
        $inspector = self::nombreUsuario('u');
        $acompanantes = self::acompanantes();

        $sql = <<<SQL
            SELECT
                e.id AS expediente_id,
                e.numero_expediente,
                e.fecha_recepcion,
                e.fecha_vencimiento,
                e.fecha_alerta <= CURRENT_DATE AS mostrar_alerta,
                tpt.nombre AS procedimiento,
                {$solicitante} AS solicitante,
                COALESCE(lf.nombre_comercial, '') AS sugerido_nombre_comercial,
                COALESCE(lf.direccion, p.direccion) AS sugerido_direccion,
                a.id AS asignacion_id,
                a.inspector_id,
                {$inspector} AS inspector_nombre,
                u.is_active AS inspector_activo,
                {$acompanantes},
                a.nombre_comercial,
                a.direccion,
                a.fecha_programada,
                a.indicaciones,
                a.fecha_asignacion,
                {$this->camposUltimaActa()}
            FROM expedientes e
            JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
            JOIN personas p ON p.id = e.solicitante_id
            LEFT JOIN LATERAL (
                SELECT nombre_comercial, direccion FROM licencias_funcionamiento
                WHERE expediente_id = e.id ORDER BY id DESC LIMIT 1
            ) lf ON TRUE
            LEFT JOIN inspecciones_asignaciones a ON a.expediente_id = e.id AND a.fecha_reasignacion IS NULL
            LEFT JOIN auth_user u ON u.id = a.inspector_id
            {$this->ultimaActa()}
            -- Incluye los que ya tienen dictamen final: la oficina aún debe emitir el certificado o denegarlo
            WHERE {$this->esperaInspeccion(sinDictamenFinal: false)}
            -- Primero los que faltan asignar; dentro de cada grupo, el que vence antes
            ORDER BY (a.id IS NULL) DESC, e.fecha_vencimiento, e.numero_expediente
            SQL;

        return $this->filas(DB::select($sql));
    }

    public function inspectoresDisponibles(): array
    {
        $nombre = self::nombreUsuario('u');

        $sql = <<<SQL
            SELECT u.id, u.username, {$nombre} AS nombre,
                (SELECT COUNT(*) FROM inspecciones_asignaciones a
                    JOIN expedientes e ON e.id = a.expediente_id
                    JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
                    WHERE a.inspector_id = u.id AND a.fecha_reasignacion IS NULL AND {$this->esperaInspeccion()}
                ) AS pendientes,
                (SELECT COUNT(*) FROM inspecciones_asignaciones_acompanantes aa
                    JOIN inspecciones_asignaciones a ON a.id = aa.asignacion_id AND a.fecha_reasignacion IS NULL
                    JOIN expedientes e ON e.id = a.expediente_id
                    JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
                    WHERE aa.inspector_id = u.id AND aa.fecha_retiro IS NULL AND {$this->esperaInspeccion()}
                ) AS acompanando
            FROM auth_user u
            JOIN usuarios_perfiles up ON up.user_id = u.id AND up.es_inspector = TRUE
            WHERE u.is_active = TRUE
            ORDER BY nombre
            SQL;

        return array_map(function (array $fila): array {
            $fila['pendientes'] = (int) $fila['pendientes'];
            $fila['acompanando'] = (int) $fila['acompanando'];

            return $fila;
        }, $this->filas(DB::select($sql)));
    }

    public function esInspectorActivo(int $usuarioId): bool
    {
        return DB::table('auth_user as u')
            ->join('usuarios_perfiles as up', 'up.user_id', '=', 'u.id')
            ->where('u.id', $usuarioId)
            ->where('u.is_active', true)
            ->where('up.es_inspector', true)
            ->exists();
    }

    public function expedienteEsperaInspeccion(int $expedienteId): bool
    {
        return DB::selectOne(<<<SQL
            SELECT 1 AS si FROM expedientes e
            JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
            WHERE e.id = ? AND {$this->esperaInspeccion()}
            SQL, [$expedienteId]) !== null;
    }

    public function expedientePendienteDeResolucion(int $expedienteId): bool
    {
        return DB::selectOne(<<<SQL
            SELECT 1 AS si FROM expedientes e
            JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
            WHERE e.id = ? AND {$this->esperaInspeccion(sinDictamenFinal: false)}
            SQL, [$expedienteId]) !== null;
    }

    public function numeroExpediente(int $expedienteId): ?int
    {
        $numero = DB::table('expedientes')->where('id', $expedienteId)->value('numero_expediente');

        return $numero === null ? null : (int) $numero;
    }

    public function buscar(int $asignacionId): ?array
    {
        $filas = $this->consultarAsignaciones('a.id = ?', [$asignacionId]);

        return $filas[0] ?? null;
    }

    public function tieneAsignacionVigente(int $expedienteId): bool
    {
        return DB::table('inspecciones_asignaciones')
            ->where('expediente_id', $expedienteId)
            ->whereNull('fecha_reasignacion')
            ->exists();
    }

    public function crear(int $expedienteId, DatosAsignacion $datos, int $usuarioId): int
    {
        try {
            return DB::transaction(function () use ($expedienteId, $datos, $usuarioId): int {
                $id = (int) DB::table('inspecciones_asignaciones')->insertGetId([
                    'expediente_id' => $expedienteId,
                    'inspector_id' => $datos->inspectorId,
                    'nombre_comercial' => $datos->nombreComercial,
                    'direccion' => $datos->direccion,
                    'fecha_programada' => $datos->fechaProgramada,
                    'indicaciones' => $datos->indicaciones,
                    'fecha_asignacion' => now(),
                    'asignado_por' => $usuarioId,
                ]);
                $this->agregarAcompanantes($id, $datos->acompanantes, $usuarioId);

                return $id;
            });
        } catch (QueryException $e) {
            // Dos jefes asignando el mismo expediente a la vez: el índice único lo detiene
            if (($e->errorInfo[0] ?? null) === '23505') {
                throw ExpedienteYaAsignado::numero((int) $this->numeroExpediente($expedienteId));
            }
            throw $e;
        }
    }

    public function reasignar(int $asignacionId, int $nuevoInspectorId, array $acompanantes, string $motivo, ?string $fechaProgramada, ?string $indicaciones, int $usuarioId): int
    {
        return DB::transaction(function () use ($asignacionId, $nuevoInspectorId, $acompanantes, $motivo, $fechaProgramada, $indicaciones, $usuarioId): int {
            $actual = DB::table('inspecciones_asignaciones')
                ->where('id', $asignacionId)
                ->whereNull('fecha_reasignacion')
                ->lockForUpdate()
                ->first();
            if ($actual === null) {
                throw AsignacionNoEncontrada::id($asignacionId);
            }

            DB::table('inspecciones_asignaciones')->where('id', $asignacionId)->update([
                'fecha_reasignacion' => now(),
                'reasignado_por' => $usuarioId,
                'motivo_reasignacion' => $motivo,
            ]);

            // El equipo de la asignación cerrada queda como estaba: es parte de su constancia
            $nueva = (int) DB::table('inspecciones_asignaciones')->insertGetId([
                'expediente_id' => $actual->expediente_id,
                'inspector_id' => $nuevoInspectorId,
                'nombre_comercial' => $actual->nombre_comercial,
                'direccion' => $actual->direccion,
                'fecha_programada' => $fechaProgramada,
                'indicaciones' => $indicaciones,
                'fecha_asignacion' => now(),
                'asignado_por' => $usuarioId,
            ]);
            $this->agregarAcompanantes($nueva, $acompanantes, $usuarioId);

            return $nueva;
        });
    }

    public function actualizar(int $asignacionId, DatosAsignacion $datos, int $usuarioId): void
    {
        DB::transaction(function () use ($asignacionId, $datos, $usuarioId): void {
            $vigente = DB::table('inspecciones_asignaciones')
                ->where('id', $asignacionId)
                ->whereNull('fecha_reasignacion')
                ->update([
                    'nombre_comercial' => $datos->nombreComercial,
                    'direccion' => $datos->direccion,
                    'fecha_programada' => $datos->fechaProgramada,
                    'indicaciones' => $datos->indicaciones,
                ]);
            if ($vigente === 0) {
                return;
            }

            // Quien sale del equipo queda con fecha y usuario; solo se agregan los nuevos
            $actuales = $this->acompanantesVigentes($asignacionId);
            DB::table('inspecciones_asignaciones_acompanantes')
                ->where('asignacion_id', $asignacionId)
                ->whereNull('fecha_retiro')
                ->whereIn('inspector_id', array_values(array_diff($actuales, $datos->acompanantes)))
                ->update(['fecha_retiro' => now(), 'retirado_por' => $usuarioId]);
            $this->agregarAcompanantes($asignacionId, array_values(array_diff($datos->acompanantes, $actuales)), $usuarioId);
        });
    }

    public function acompanantesVigentes(int $asignacionId): array
    {
        return DB::table('inspecciones_asignaciones_acompanantes')
            ->where('asignacion_id', $asignacionId)
            ->whereNull('fecha_retiro')
            ->orderBy('id')
            ->pluck('inspector_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    /** @param  array<int, int>  $inspectores */
    private function agregarAcompanantes(int $asignacionId, array $inspectores, int $usuarioId): void
    {
        foreach ($inspectores as $inspectorId) {
            DB::table('inspecciones_asignaciones_acompanantes')->insert([
                'asignacion_id' => $asignacionId,
                'inspector_id' => $inspectorId,
                'fecha_agregado' => now(),
                'agregado_por' => $usuarioId,
            ]);
        }
    }

    public function historial(int $expedienteId): array
    {
        return $this->consultarAsignaciones('a.expediente_id = ?', [$expedienteId]);
    }

    public function bandeja(int $inspectorId): array
    {
        // Las que tiene a cargo y las que acompaña
        return $this->consultarAsignaciones(
            <<<SQL
                a.fecha_reasignacion IS NULL AND {$this->esperaInspeccion()}
                AND (a.inspector_id = ? OR EXISTS (
                    SELECT 1 FROM inspecciones_asignaciones_acompanantes aa
                    WHERE aa.asignacion_id = a.id AND aa.inspector_id = ? AND aa.fecha_retiro IS NULL
                ))
                SQL,
            [$inspectorId, $inspectorId, $inspectorId],
            'a.fecha_programada NULLS LAST, e.fecha_vencimiento, e.numero_expediente',
            esResponsable: true,
        );
    }

    /**
     * @param  array<int, mixed>  $parametros  con esResponsable, el primero es el inspector que consulta
     * @return array<int, array<string, mixed>>
     */
    private function consultarAsignaciones(string $condicion, array $parametros, string $orden = 'a.id DESC', bool $esResponsable = false): array
    {
        $solicitante = self::NOMBRE_SOLICITANTE;
        $inspector = self::nombreUsuario('u');
        $asignador = self::nombreUsuario('ua');
        $reasignador = self::nombreUsuario('ur');
        $acompanantes = self::acompanantes();
        $responsable = $esResponsable ? 'a.inspector_id = ? AS es_responsable,' : '';

        $sql = <<<SQL
            SELECT
                {$responsable}
                a.id,
                a.expediente_id,
                e.numero_expediente,
                e.fecha_recepcion,
                e.fecha_vencimiento,
                tpt.nombre AS procedimiento,
                {$solicitante} AS solicitante,
                p.telefono AS solicitante_telefono,
                a.inspector_id,
                {$inspector} AS inspector_nombre,
                {$acompanantes},
                a.nombre_comercial,
                a.direccion,
                a.fecha_programada,
                a.indicaciones,
                a.fecha_asignacion,
                {$asignador} AS asignado_por,
                a.fecha_reasignacion IS NULL AS vigente,
                a.fecha_reasignacion,
                {$reasignador} AS reasignado_por,
                a.motivo_reasignacion,
                {$this->camposUltimaActa()}
            FROM inspecciones_asignaciones a
            JOIN expedientes e ON e.id = a.expediente_id
            JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
            JOIN personas p ON p.id = e.solicitante_id
            JOIN auth_user u ON u.id = a.inspector_id
            JOIN auth_user ua ON ua.id = a.asignado_por
            LEFT JOIN auth_user ur ON ur.id = a.reasignado_por
            {$this->ultimaActa()}
            WHERE {$condicion}
            ORDER BY {$orden}
            SQL;

        return $this->filas(DB::select($sql, $parametros));
    }

    /** Por defecto también exige que falte el dictamen final (todavía hay que ir al local) */
    private function esperaInspeccion(bool $sinDictamenFinal = true): string
    {
        return '('.self::ESPERA_INSPECCION.($sinDictamenFinal ? ' AND '.self::SIN_DICTAMEN_FINAL : '').')';
    }

    private function ultimaActa(): string
    {
        return self::ULTIMA_ACTA;
    }

    private function camposUltimaActa(): string
    {
        return self::CAMPOS_ULTIMA_ACTA;
    }

    /**
     * @param  array<int, mixed>  $filas
     * @return array<int, array<string, mixed>>
     */
    private function filas(array $filas): array
    {
        return array_map(static function ($fila): array {
            $fila = (array) $fila;
            if (isset($fila['acompanantes']) && is_string($fila['acompanantes'])) {
                $fila['acompanantes'] = json_decode($fila['acompanantes'], true, flags: JSON_THROW_ON_ERROR);
            }

            return $fila;
        }, $filas);
    }
}
