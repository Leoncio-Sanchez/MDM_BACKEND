<?php

declare(strict_types=1);

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PersonaRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscarPorFiltro(string $filtro, string $valor): array
    {
        $filtro = strtoupper(trim($filtro));

        $sqlNombre = "
            SELECT p.id AS persona_id
            FROM personas p
            WHERE TRIM(
                COALESCE(p.apellido_paterno, '') || ' ' ||
                COALESCE(p.apellido_materno, '') || ' ' ||
                COALESCE(p.nombres, '')
            ) ILIKE ?
        ";

        $sqlDocumento = '
            SELECT DISTINCT pd.persona_id
            FROM personas_documentos pd
            WHERE pd.numero_documento = ?
        ';

        $sqlId = '
            SELECT ?::INTEGER AS persona_id
        ';

        $subquery = match ($filtro) {
            'NOMBRE' => $sqlNombre,
            'DOCUMENTO' => $sqlDocumento,
            'ID' => $sqlId,
            default => throw new \InvalidArgumentException("Filtro '{$filtro}' no válido. Opciones: NOMBRE, DOCUMENTO, ID"),
        };

        $valorParam = match ($filtro) {
            'NOMBRE' => '%'.str_replace(' ', '%', $valor).'%',
            default => $valor,
        };

        $sql = "
        WITH personas_filtradas AS (
            {$subquery}
        ),
        documentos_concatenados AS (
            SELECT
                pf.persona_id,
                STRING_AGG(
                    (tdi.nombre || ' ' || pd.numero_documento)::text,
                    ', '::text
                    ORDER BY tdi.nombre || ' ' || pd.numero_documento
                ) AS documento_concatenado
            FROM personas_filtradas pf
            LEFT JOIN personas_documentos pd
                   ON pf.persona_id = pd.persona_id
            LEFT JOIN tipos_documento_identidad tdi
                   ON pd.tipo_documento_identidad_id = tdi.id
            GROUP BY pf.persona_id
        )
        SELECT
            p.id,
            p.tipo_persona,
            p.apellido_paterno,
            p.apellido_materno,
            p.nombres,
            TRIM(
                COALESCE(p.apellido_paterno, '') || ' ' ||
                COALESCE(p.apellido_materno, '') || ' ' ||
                COALESCE(p.nombres, '')
            )                       AS persona_nombre,
            p.direccion,
            p.distrito,
            p.provincia,
            p.departamento,
            p.telefono,
            p.correo_electronico,
            p.fecha_creacion,
            p.fecha_actualizacion,
            p.user_id,
            dc.documento_concatenado
        FROM documentos_concatenados dc
        INNER JOIN personas p ON dc.persona_id = p.id
        ORDER BY TRIM(
            COALESCE(p.apellido_paterno, '') || ' ' ||
            COALESCE(p.apellido_materno, '') || ' ' ||
            COALESCE(p.nombres, '')
        )
        ";

        $results = DB::select($sql, [$valorParam]);

        return array_map(function ($row): array {
            $arr = (array) $row;
            foreach (['fecha_creacion', 'fecha_actualizacion'] as $campo) {
                if (! empty($arr[$campo])) {
                    $c = Carbon::parse($arr[$campo])->setTimezone('UTC');
                    $fmt = ((int) $c->format('u')) > 0 ? 'Y-m-d\TH:i:s.u\Z' : 'Y-m-d\TH:i:s\Z';
                    $arr[$campo] = $c->format($fmt);
                }
            }

            return $arr;
        }, $results);
    }

    public function tieneDependientes(int $id): bool
    {
        $hasExpedientes = DB::table('expedientes')
            ->where('solicitante_id', $id)
            ->orWhere('representante_id', $id)
            ->exists();

        $hasLicencias = DB::table('licencias_funcionamiento')
            ->where('titular_id', $id)
            ->orWhere('conductor_id', $id)
            ->exists();

        $hasItse = DB::table('itse')
            ->where('titular_id', $id)
            ->orWhere('conductor_id', $id)
            ->exists();

        return $hasExpedientes || $hasLicencias || $hasItse;
    }
}
