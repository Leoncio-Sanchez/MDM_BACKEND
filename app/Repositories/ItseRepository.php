<?php

declare(strict_types=1);

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ItseRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscarConFiltros(string $filtro, string $valor): array
    {
        $filtroUpper = strtoupper(trim($filtro));

        if ($filtroUpper === 'FECHA_EMISION') {
            $filtroUpper = 'FECHA_EXPEDICION';
        }

        $filtrosValidos = [
            'ID',
            'NUMERO',
            'EXPEDIENTE',
            'NOMBRE_COMERCIAL',
            'FECHA_EXPEDICION',
            'NOMBRES_TITULAR',
            'RUC_TITULAR',
            'NOMBRES_CONDUCTOR',
            'DIRECCION',
            'RECIBO_PAGO',
            'RESOLUCION_NUMERO',
        ];

        if (! in_array($filtroUpper, $filtrosValidos, true)) {
            throw new \InvalidArgumentException("Filtro '{$filtroUpper}' no válido.");
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
                $whereClause = 'WHERE i.id = ?';
                $params = [(int) $valor];
                break;
            case 'NUMERO':
                $whereClause = 'WHERE i.numero_itse = ?';
                $params = [(int) $valor];
                break;
            case 'EXPEDIENTE':
                $whereClause = 'WHERE e.numero_expediente = ?';
                $params = [(int) $valor];
                break;
            case 'NOMBRE_COMERCIAL':
                $whereClause = 'WHERE i.nombre_comercial ILIKE ?';
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'FECHA_EXPEDICION':
                $whereClause = 'WHERE i.fecha_expedicion = ?';
                $params = [$valor];
                break;
            case 'NOMBRES_TITULAR':
                $whereClause = "WHERE (COALESCE(ttitular.apellido_paterno, '') || ' ' || COALESCE(ttitular.apellido_materno, '') || ' ' || COALESCE(ttitular.nombres, '')) ILIKE ?";
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'RUC_TITULAR':
                $whereClause = 'WHERE truc.numero_documento = ?';
                $params = [$valor];
                break;
            case 'NOMBRES_CONDUCTOR':
                $whereClause = "WHERE (COALESCE(tconductor.apellido_paterno, '') || ' ' || COALESCE(tconductor.apellido_materno, '') || ' ' || COALESCE(tconductor.nombres, '')) ILIKE ?";
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'DIRECCION':
                $whereClause = 'WHERE i.direccion ILIKE ?';
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'RECIBO_PAGO':
                $whereClause = 'WHERE i.numero_recibo_pago ILIKE ?';
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
            case 'RESOLUCION_NUMERO':
                $whereClause = 'WHERE i.resolucion_numero ILIKE ?';
                $params = ['%'.str_replace(' ', '%', $valor).'%'];
                break;
        }

        $sql = "
SELECT
    i.id,
    i.expediente_id,
    i.tipo_itse_id,
    i.numero_itse,
    i.fecha_expedicion,
    i.fecha_solicitud_renovacion,
    i.fecha_caducidad,
    i.titular_id,
    i.conductor_id,
    i.itse_principal_id,
    i.nombre_comercial,
    i.nivel_riesgo_id,
    i.direccion,
    i.resolucion_numero,
    i.area,
    i.numero_recibo_pago,
    i.observaciones,
    i.se_puede_publicar,
    i.capacidad_aforo,
    i.fecha_notificacion,
    i.usuario_id,
    i.fecha_digitacion,
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
        WHEN titse_inactivos.itse_id IS NULL THEN TRUE
        ELSE FALSE
    END AS esta_activo,
    CASE i.tipo_itse_id
        WHEN 1 THEN 'ESTÁNDAR'
        WHEN 2 THEN 'RENOVACIÓN'
        ELSE 'DESCONOCIDO'
    END AS tipo_itse_nombre,
    nr.nombre AS nivel_riesgo_nombre
FROM itse i
LEFT JOIN expedientes e
    ON i.expediente_id = e.id
LEFT JOIN personas AS ttitular
    ON i.titular_id = ttitular.id
LEFT JOIN personas AS tconductor
    ON i.conductor_id = tconductor.id
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
    ON i.titular_id = truc.persona_id
LEFT JOIN (
    SELECT DISTINCT ie.itse_id
    FROM itse_estados ie
    INNER JOIN estados est
        ON ie.estado_id = est.id
    WHERE est.esta_activo = FALSE
) AS titse_inactivos
    ON i.id = titse_inactivos.itse_id
LEFT JOIN niveles_riesgo nr
    ON i.nivel_riesgo_id = nr.id
{$whereClause}
ORDER BY i.numero_itse DESC
";

        $rows = DB::select($sql, $params);

        return array_map(function ($row) {
            $arr = (array) $row;
            if (! empty($arr['fecha_digitacion'])) {
                $arr['fecha_digitacion'] = Carbon::parse($arr['fecha_digitacion'])
                    ->setTimezone('America/Lima')
                    ->format('Y-m-d\TH:i:s.uP');
            }
            if (! empty($arr['fecha_recepcion'])) {
                $arr['fecha_recepcion'] = Carbon::parse($arr['fecha_recepcion'])
                    ->setTimezone('America/Lima')
                    ->format('Y-m-d\TH:i:s.uP');
            }

            return $arr;
        }, $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buscarSqlite(string $filtro, string $valor): array
    {
        $query = DB::table('itse as i')
            ->select([
                'i.*',
            ])
            ->orderBy('i.numero_itse', 'desc');

        switch ($filtro) {
            case 'ID':
                $query->where('i.id', (int) $valor);
                break;
            case 'NUMERO':
                $query->where('i.numero_itse', (int) $valor);
                break;
            case 'EXPEDIENTE':
                $query->where('i.expediente_id', (int) $valor);
                break;
            case 'NOMBRE_COMERCIAL':
                $query->where('i.nombre_comercial', 'LIKE', '%'.$valor.'%');
                break;
            case 'FECHA_EXPEDICION':
                $query->where('i.fecha_expedicion', $valor);
                break;
            default:
                break;
        }

        $rows = $query->limit(50)->get();

        return $rows->map(fn ($r) => (array) $r)->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarEstados(int $itseId): array
    {
        $sql = '
SELECT
    ie.id,
    ie.itse_id,
    ie.estado_id,
    ie.fecha_estado,
    ie.documento,
    ie.observaciones,
    ie.usuario_id,
    ie.fecha_digitacion,
    est.nombre  AS estado_nombre,
    est.es_para_lf,
    est.es_para_itse,
    est.esta_activo
FROM itse_estados ie
LEFT JOIN estados est
    ON ie.estado_id = est.id
WHERE ie.itse_id = ?
ORDER BY ie.fecha_digitacion DESC
';

        $rows = DB::select($sql, [$itseId]);

        return array_map(function ($r) {
            $arr = (array) $r;
            if (! empty($arr['fecha_digitacion'])) {
                $arr['fecha_digitacion'] = Carbon::parse($arr['fecha_digitacion'])
                    ->setTimezone('America/Lima')
                    ->format('Y-m-d\TH:i:s.uP');
            }
            if (! empty($arr['fecha_estado'])) {
                $arr['fecha_estado'] = Carbon::parse($arr['fecha_estado'])->toDateString();
            }

            return $arr;
        }, $rows);
    }
}
