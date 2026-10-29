<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Repositories;

use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\ValueObjects\AnulacionExpediente;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\AutorizacionImprocedenteModel;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\ExpedienteAnulacionModel;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\ExpedienteModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class EloquentExpedienteRepository implements ExpedienteRepositoryInterface
{
    /**
     * @return array<int, Expediente>
     */
    public function listar(): array
    {
        /** @var Collection<int, ExpedienteModel> $models */
        $models = ExpedienteModel::with('anulacion.usuario')->orderBy('id', 'desc')->get();

        return $models->map(fn (ExpedienteModel $m): Expediente => $this->toEntity($m))->all();
    }

    /**
     * @return array{items: array<int, Expediente>, total: int}
     */
    public function paginar(int $pagina = 1, int $porPagina = 15): array
    {
        $query = ExpedienteModel::with('anulacion.usuario')->orderBy('id', 'desc');
        $total = $query->count();
        /** @var Collection<int, ExpedienteModel> $models */
        $models = $query->skip(($pagina - 1) * $porPagina)->take($porPagina)->get();
        $items = $models->map(fn (ExpedienteModel $m): Expediente => $this->toEntity($m))->all();

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    public function buscarPorId(int $id): ?Expediente
    {
        /** @var ExpedienteModel|null $model */
        $model = ExpedienteModel::with(['autorizacionesImprocedentes', 'anulacion.usuario'])->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function crear(Expediente $expediente): Expediente
    {
        $model = new ExpedienteModel;
        $model->uuid = $expediente->uuid;
        $model->numero_expediente = $expediente->numeroExpediente;
        $model->fecha_recepcion = $this->toUtc($expediente->fechaRecepcion);
        $model->tipo_procedimiento_tupa_id = $expediente->tipoProcedimientoTupaId;
        $model->solicitante_id = $expediente->solicitanteId;
        $model->representante_id = $expediente->representanteId;
        $model->observaciones = $expediente->observaciones;
        $model->fecha_vencimiento = $expediente->fechaVencimiento->format('Y-m-d');
        $model->fecha_alerta = $expediente->fechaAlerta->format('Y-m-d');
        $model->fecha_suspension = $expediente->fechaSuspension?->format('Y-m-d');
        $model->dias_ampliacion = $expediente->diasAmpliacion;
        $model->motivo_ampliacion = $expediente->motivoAmpliacion;
        $model->usuario_ampliacion = $expediente->usuarioAmpliacionId;
        $model->fecha_digitacion_ampliacion = $expediente->fechaDigitacionAmpliacion ? $this->toUtc($expediente->fechaDigitacionAmpliacion) : null;
        $model->usuario_id = $expediente->usuarioId;
        $model->fecha_digitacion = $this->toUtc($expediente->fechaDigitacion);
        $model->save();

        return $this->toEntity($model);
    }

    public function actualizar(Expediente $expediente): Expediente
    {
        /** @var ExpedienteModel $model */
        $model = ExpedienteModel::findOrFail($expediente->id);
        $model->numero_expediente = $expediente->numeroExpediente;
        $model->fecha_recepcion = $this->toUtc($expediente->fechaRecepcion);
        $model->tipo_procedimiento_tupa_id = $expediente->tipoProcedimientoTupaId;
        $model->solicitante_id = $expediente->solicitanteId;
        $model->representante_id = $expediente->representanteId;
        $model->observaciones = $expediente->observaciones;
        $model->fecha_vencimiento = $expediente->fechaVencimiento->format('Y-m-d');
        $model->fecha_alerta = $expediente->fechaAlerta->format('Y-m-d');
        $model->fecha_suspension = $expediente->fechaSuspension?->format('Y-m-d');
        $model->dias_ampliacion = $expediente->diasAmpliacion;
        $model->motivo_ampliacion = $expediente->motivoAmpliacion;
        $model->usuario_ampliacion = $expediente->usuarioAmpliacionId;
        $model->fecha_digitacion_ampliacion = $expediente->fechaDigitacionAmpliacion ? $this->toUtc($expediente->fechaDigitacionAmpliacion) : null;
        $model->save();

        return $this->toEntity($model);
    }

    public function anular(int $id, AnulacionExpediente $anulacion): void
    {
        ExpedienteAnulacionModel::query()->create([
            'expediente_id' => $id,
            'motivo' => trim($anulacion->motivo),
            'documento' => $anulacion->documento,
            'fecha_anulacion' => $this->toUtc($anulacion->fechaAnulacion),
            'usuario_id' => $anulacion->usuarioId,
        ]);
    }

    public function existeNumeroEnAnio(int $numero, int $anio, ?int $exceptoId = null): bool
    {
        $query = ExpedienteModel::query()
            ->where('numero_expediente', $numero)
            ->whereYear('fecha_recepcion', $anio);

        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }

        return $query->exists();
    }

    public function siguienteNumeroEnAnio(int $anio): int
    {
        $max = ExpedienteModel::query()
            ->whereYear('fecha_recepcion', $anio)
            ->max('numero_expediente');

        return ((int) $max) + 1;
    }

    public function tieneLicencia(int $expedienteId): bool
    {
        return DB::table('licencias_funcionamiento')
            ->where('expediente_id', $expedienteId)
            ->exists();
    }

    public function tieneItse(int $expedienteId): bool
    {
        return DB::table('itse')
            ->where('expediente_id', $expedienteId)
            ->exists();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscarPorFiltro(string $filtro, string $valor): array
    {
        [$whereClause, $param] = match ($filtro) {
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
            'RUC_SOLICITANTE' => ['WHERE tsolicitante_ruc.numero_documento = ?', (string) $valor],
            default => throw new \InvalidArgumentException("Filtro '{$filtro}' no válido. Opciones: ID, NUMERO, FECHA_RECEPCION, FECHA_VENCIMIENTO, NOMBRE_SOLICITANTE, RUC_SOLICITANTE"),
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
        tsolicitante_ruc.numero_documento AS solicitante_ruc,
        ea.id IS NOT NULL             AS anulado,
        ea.motivo                     AS motivo_anulacion
    FROM expedientes
    LEFT JOIN expedientes_anulaciones ea
        ON ea.expediente_id = expedientes.id
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
        WHEN t.anulado THEN FALSE
        WHEN t.requiere_lf = FALSE THEN FALSE
        WHEN t.requiere_lf = TRUE AND lf.id   IS NOT NULL THEN FALSE
        WHEN t.requiere_lf = TRUE AND tlf.id  IS NOT NULL THEN FALSE
        ELSE TRUE
    END AS licencia_pendiente,
    CASE
        WHEN t.anulado THEN FALSE
        WHEN t.requiere_itse = FALSE THEN FALSE
        WHEN t.requiere_itse = TRUE AND i.id    IS NOT NULL THEN FALSE
        WHEN t.requiere_itse = TRUE AND titse.id IS NOT NULL THEN FALSE
        ELSE TRUE
    END AS itse_pendiente,
    (NOT t.anulado AND t.fecha_alerta <= CURRENT_DATE) AS mostrar_alerta
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

        /** @var array<int, \stdClass> $rows */
        $rows = DB::select($sqlFinal, [$param]);

        return array_map(static fn (\stdClass $row): array => (array) $row, $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPendientes(): array
    {
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
WHERE (texpedientes.licencia_pendiente = TRUE
   OR texpedientes.itse_pendiente = TRUE)
  -- Un expediente anulado ya no está en trámite
  AND NOT EXISTS (SELECT 1 FROM expedientes_anulaciones ea WHERE ea.expediente_id = e.id)
ORDER BY e.fecha_alerta DESC
SQL;

        /** @var array<int, \stdClass> $rows */
        $rows = DB::select($sql);

        return array_map(static fn (\stdClass $row): array => (array) $row, $rows);
    }

    /**
     * @return array<int, Expediente>
     */
    public function consultar(?int $numeroExpediente): array
    {
        $query = ExpedienteModel::with(['autorizacionesImprocedentes', 'anulacion.usuario'])->orderBy('id', 'desc');

        if ($numeroExpediente !== null) {
            $query->where('numero_expediente', $numeroExpediente);
        }

        /** @var Collection<int, ExpedienteModel> $models */
        $models = $query->get();

        return $models->map(fn (ExpedienteModel $m): Expediente => $this->toEntity($m))->all();
    }

    /**
     * Las columnas datetime se guardan en UTC (igual que Django con USE_TZ) y se leen como UTC
     * (config app.timezone). Sin esta conversión, una fecha en hora de Lima se guardaba con su
     * hora local y al leerla se desplazaba 5 horas (el 12 aparecía como el 11 a las 19:00).
     */
    private function toUtc(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->setTimezone(new \DateTimeZone('UTC'));
    }

    private function toDateTimeImmutable(\DateTimeInterface|string $date): DateTimeImmutable
    {
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }
        if ($date instanceof \DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($date);
        }

        return new DateTimeImmutable($date, new \DateTimeZone('America/Lima'));
    }

    private function toNullableDateTimeImmutable(\DateTimeInterface|string|null $date): ?DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        return $this->toDateTimeImmutable($date);
    }

    private function toEntity(ExpedienteModel $model): Expediente
    {
        $autorizaciones = [];
        if ($model->relationLoaded('autorizacionesImprocedentes')) {
            /** @var AutorizacionImprocedenteModel $autModel */
            foreach ($model->autorizacionesImprocedentes as $autModel) {
                $autorizaciones[] = new AutorizacionImprocedente(
                    id: (int) $autModel->id,
                    expedienteId: (int) $autModel->expediente_id,
                    tipoAutorizacion: TipoAutorizacion::from($autModel->tipo_autorizacion),
                    fechaRechazo: $this->toDateTimeImmutable($autModel->fecha_rechazo),
                    documento: $autModel->documento !== null ? (string) $autModel->documento : null,
                    observaciones: $autModel->observaciones !== null ? (string) $autModel->observaciones : null,
                    usuarioId: (int) $autModel->usuario_id,
                    fechaDigitacion: $this->toDateTimeImmutable($autModel->fecha_digitacion),
                );
            }
        }

        $anulacion = null;
        if ($model->relationLoaded('anulacion') && $model->anulacion !== null) {
            /** @var ExpedienteAnulacionModel $a */
            $a = $model->anulacion;
            $usuario = $a->usuario;
            $anulacion = new AnulacionExpediente(
                motivo: (string) $a->motivo,
                documento: $a->documento !== null ? (string) $a->documento : null,
                fechaAnulacion: $this->toDateTimeImmutable($a->fecha_anulacion),
                usuarioId: (int) $a->usuario_id,
                usuarioNombre: $usuario !== null
                    ? (trim($usuario->first_name.' '.$usuario->last_name) ?: (string) $usuario->username)
                    : null,
            );
        }

        return new Expediente(
            id: (int) $model->id,
            uuid: (string) $model->uuid,
            numeroExpediente: (int) $model->numero_expediente,
            fechaRecepcion: $this->toDateTimeImmutable($model->fecha_recepcion),
            tipoProcedimientoTupaId: (int) $model->tipo_procedimiento_tupa_id,
            solicitanteId: (int) $model->solicitante_id,
            representanteId: $model->representante_id !== null ? (int) $model->representante_id : null,
            observaciones: $model->observaciones !== null ? (string) $model->observaciones : null,
            fechaVencimiento: $this->toDateTimeImmutable($model->fecha_vencimiento),
            fechaAlerta: $this->toDateTimeImmutable($model->fecha_alerta),
            fechaSuspension: $this->toNullableDateTimeImmutable($model->fecha_suspension),
            diasAmpliacion: $model->dias_ampliacion !== null ? (int) $model->dias_ampliacion : null,
            motivoAmpliacion: $model->motivo_ampliacion !== null ? (string) $model->motivo_ampliacion : null,
            usuarioAmpliacionId: $model->usuario_ampliacion !== null ? (int) $model->usuario_ampliacion : null,
            fechaDigitacionAmpliacion: $this->toNullableDateTimeImmutable($model->fecha_digitacion_ampliacion),
            usuarioId: (int) $model->usuario_id,
            fechaDigitacion: $this->toDateTimeImmutable($model->fecha_digitacion),
            autorizacionesImprocedentes: $autorizaciones,
            anulacion: $anulacion,
        );
    }
}
