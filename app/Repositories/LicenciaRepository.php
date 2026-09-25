<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class LicenciaRepository
{
    /**
     * Busca licencias aplicando el filtro exacto de Django (_SQL_BUSCAR_LF)
     *
     * @return array<int, array<string, mixed>>
     */
    public function buscarConFiltros(string $filtro, string $valor): array
    {
        $filtroUpper = strtoupper(trim($filtro));

        // Normalizar alias de filtro
        if ($filtroUpper === 'NUMERO_LICENCIA') {
            $filtroUpper = 'NUMERO';
        }

        $filtrosValidos = [
            'ID',
            'NUMERO',
            'EXPEDIENTE',
            'NOMBRE_COMERCIAL',
            'FECHA_EMISION',
            'NOMBRES_TITULAR',
            'RUC_TITULAR',
            'NOMBRES_CONDUCTOR',
            'DIRECCION',
            'RECIBO_PAGO',
            'RESOLUCION_NUMERO',
        ];

        if (! in_array($filtroUpper, $filtrosValidos, true)) {
            throw new \InvalidArgumentException(
                "Filtro '{$filtroUpper}' no válido. Opciones: ".implode(', ', $filtrosValidos)
            );
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return $this->buscarPostgres($filtroUpper, $valor);
        }

        return $this->buscarSqlite($filtroUpper, $valor);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buscarPostgres(string $filtro, string $valor): array
    {
        $whereClause = '';
        $params = [];

        switch ($filtro) {
            case 'ID':
                $whereClause = 'WHERE lf.id = ?';
                $params = [(int) $valor];
                break;
            case 'NUMERO':
                $whereClause = 'WHERE lf.numero_licencia = ?';
                $params = [(int) $valor];
                break;
            case 'EXPEDIENTE':
                $whereClause = 'WHERE e.numero_expediente = ?';
                $params = [(int) $valor];
                break;
            case 'NOMBRE_COMERCIAL':
                $whereClause = 'WHERE lf.nombre_comercial ILIKE ?';
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'FECHA_EMISION':
                $whereClause = 'WHERE lf.fecha_emision = ?';
                $params = [$valor];
                break;
            case 'NOMBRES_TITULAR':
                $whereClause = "WHERE TRIM(COALESCE(ttitular.apellido_paterno, '') || ' ' || COALESCE(ttitular.apellido_materno, '') || ' ' || COALESCE(ttitular.nombres, '')) ILIKE ?";
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'RUC_TITULAR':
                $whereClause = 'WHERE truc.numero_documento = ?';
                $params = [$valor];
                break;
            case 'NOMBRES_CONDUCTOR':
                $whereClause = "WHERE TRIM(COALESCE(tconductor.apellido_paterno, '') || ' ' || COALESCE(tconductor.apellido_materno, '') || ' ' || COALESCE(tconductor.nombres, '')) ILIKE ?";
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'DIRECCION':
                $whereClause = 'WHERE TRIM(lf.direccion) ILIKE ?';
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'RECIBO_PAGO':
                $whereClause = 'WHERE TRIM(lf.numero_recibo_pago) ILIKE ?';
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'RESOLUCION_NUMERO':
                $whereClause = 'WHERE TRIM(lf.resolucion_numero) ILIKE ?';
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
        }

        $sql = "
SELECT
    lf.id,
    lf.expediente_id,
    lf.tipo_licencia_id,
    lf.numero_licencia,
    lf.fecha_emision,
    lf.titular_id,
    lf.conductor_id,
    lf.licencia_principal_id,
    lf.nombre_comercial,
    lf.es_vigencia_indeterminada,
    lf.fecha_inicio_vigencia,
    lf.fecha_fin_vigencia,
    lf.nivel_riesgo_id,
    lf.actividad,
    lf.direccion,
    lf.hora_desde,
    lf.hora_hasta,
    lf.resolucion_numero,
    lf.zonificacion_id,
    lf.area,
    lf.numero_recibo_pago,
    lf.observaciones,
    lf.se_puede_publicar,
    lf.fecha_notificacion,
    lf.usuario_id,
    lf.fecha_digitacion,
    e.numero_expediente,
    e.fecha_recepcion,
    TRIM(
        COALESCE(ttitular.apellido_paterno, '') || ' ' ||
        COALESCE(ttitular.apellido_materno, '') || ' ' ||
        COALESCE(ttitular.nombres, '')
    ) AS titular_nombre,
    truc.numero_documento AS titular_ruc,
    TRIM(
        COALESCE(tconductor.apellido_paterno, '') || ' ' ||
        COALESCE(tconductor.apellido_materno, '') || ' ' ||
        COALESCE(tconductor.nombres, '')
    ) AS conductor_nombre,
    CASE
        WHEN tlicencias_inactivas.licencia_funcionamiento_id IS NULL THEN TRUE
        ELSE FALSE
    END AS esta_activo,
    tl.nombre  AS tipo_licencia_nombre,
    z.nombre   AS zonificacion_nombre,
    nr.nombre  AS nivel_riesgo_nombre
FROM licencias_funcionamiento lf
LEFT JOIN tipos_licencia tl
    ON lf.tipo_licencia_id = tl.id
LEFT JOIN expedientes e
    ON lf.expediente_id = e.id
LEFT JOIN personas AS ttitular
    ON lf.titular_id = ttitular.id
LEFT JOIN personas AS tconductor
    ON lf.conductor_id = tconductor.id
LEFT JOIN (
    SELECT
        pd.id,
        pd.persona_id,
        pd.numero_documento
    FROM personas_documentos pd
    INNER JOIN tipos_documento_identidad tdi
        ON pd.tipo_documento_identidad_id = tdi.id
    WHERE tdi.codigo = '06'
) AS truc
    ON lf.titular_id = truc.persona_id
LEFT JOIN (
    SELECT DISTINCT lfe.licencia_funcionamiento_id
    FROM licencias_funcionamiento_estados lfe
    INNER JOIN estados est
        ON lfe.estado_id = est.id
    WHERE est.esta_activo = FALSE
) AS tlicencias_inactivas
    ON lf.id = tlicencias_inactivas.licencia_funcionamiento_id
LEFT JOIN zonificaciones z
    ON lf.zonificacion_id = z.id
LEFT JOIN niveles_riesgo nr
    ON lf.nivel_riesgo_id = nr.id
{$whereClause}
ORDER BY lf.numero_licencia DESC
";

        $rows = DB::select($sql, $params);

        return array_map(fn ($row) => (array) $row, $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buscarSqlite(string $filtro, string $valor): array
    {
        $query = DB::table('licencias_funcionamiento as lf')
            ->select([
                'lf.*',
            ])
            ->orderBy('lf.numero_licencia', 'desc');

        switch ($filtro) {
            case 'ID':
                $query->where('lf.id', (int) $valor);
                break;
            case 'NUMERO':
                $query->where('lf.numero_licencia', (int) $valor);
                break;
            case 'EXPEDIENTE':
                $query->where('lf.expediente_id', (int) $valor);
                break;
            case 'NOMBRE_COMERCIAL':
                $query->where('lf.nombre_comercial', 'LIKE', '%'.$valor.'%');
                break;
            case 'FECHA_EMISION':
                $query->where('lf.fecha_emision', $valor);
                break;
            default:
                break;
        }

        $rows = $query->limit(50)->get();

        return $rows->map(fn ($r) => (array) $r)->all();
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array<int, array<string, mixed>>
     */
    public function generarReporte(array $filtros): array
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'pgsql') {
            $query = DB::table('licencias_funcionamiento')
                ->select(['id', 'numero_licencia', 'nombre_comercial', 'fecha_emision'])
                ->orderBy('fecha_emision', 'asc');
            if (! empty($filtros['numero_licencia'])) {
                $query->where('numero_licencia', (int) $filtros['numero_licencia']);
            }
            if (! empty($filtros['emision_desde']) && ! empty($filtros['emision_hasta'])) {
                $query->whereBetween('fecha_emision', [$filtros['emision_desde'], $filtros['emision_hasta']]);
            }

            return $query->get()->map(fn ($r) => (array) $r)->all();
        }

        $condiciones = [];
        $params = [];

        if (! empty($filtros['numero_licencia'])) {
            $condiciones[] = 'licencias_funcionamiento.numero_licencia = ?';
            $params[] = (int) $filtros['numero_licencia'];
        }

        if (! empty($filtros['emision_desde']) && ! empty($filtros['emision_hasta'])) {
            $condiciones[] = 'licencias_funcionamiento.fecha_emision BETWEEN ? AND ?';
            $params[] = $filtros['emision_desde'];
            $params[] = $filtros['emision_hasta'];
        }

        $whereClause = ! empty($condiciones) ? 'WHERE '.implode(' AND ', $condiciones) : '';

        $sql = "
SELECT
    licencias_funcionamiento.id,
    licencias_funcionamiento.numero_licencia,
    licencias_funcionamiento.nombre_comercial,
    licencias_funcionamiento.fecha_emision
FROM licencias_funcionamiento
{$whereClause}
ORDER BY licencias_funcionamiento.fecha_emision ASC
";

        $rows = DB::select($sql, $params);

        return array_map(fn ($r) => (array) $r, $rows);
    }
}
