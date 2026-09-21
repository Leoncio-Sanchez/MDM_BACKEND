<?php

declare(strict_types=1);

namespace App\Modules\Personas\Infrastructure\Persistence\Repositories;

use App\Modules\Personas\Application\Mappers\PersonaMapper;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Infrastructure\Persistence\Models\PersonaModel;
use Illuminate\Support\Facades\DB;

final class EloquentPersonaRepository implements PersonaRepositoryInterface
{
    public function __construct(
        private readonly PersonaMapper $mapper,
    ) {}

    /** @return \App\Modules\Personas\Domain\Entities\Persona[] */
    public function listarTodas(): array
    {
        $models = PersonaModel::with('documentos')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();

        return $models->map(fn (PersonaModel $m) => $this->mapper->toDomain($m))->all();
    }

    public function buscarPorId(int $id): ?Persona
    {
        $model = PersonaModel::with('documentos')->find($id);
        if (! $model) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    /** @return array<int, array<string, mixed>> */
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
            default => throw new \InvalidArgumentException('Filtro no válido.'),
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

        return array_map(fn ($row) => (array) $row, $results);
    }

    public function crear(Persona $persona): Persona
    {
        $model = PersonaModel::create($this->mapper->toPersistence($persona));
        $model->load('documentos');

        return $this->mapper->toDomain($model);
    }

    public function actualizar(Persona $persona): Persona
    {
        /** @var PersonaModel $model */
        $model = PersonaModel::findOrFail($persona->id);
        $model->update($this->mapper->toPersistence($persona));
        $model->load('documentos');

        return $this->mapper->toDomain($model);
    }

    public function eliminar(int $id): void
    {
        PersonaModel::destroy($id);
    }

    public function tieneDependientes(int $id): bool
    {
        // Django model sets on_delete=PROTECT for Expediente (solicitante_id, representante_id)
        // Licencia (titular_id, conductor_id), ITSE (titular_id, conductor_id).

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
