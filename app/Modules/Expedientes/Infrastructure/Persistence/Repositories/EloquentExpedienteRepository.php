<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Repositories;

use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\AutorizacionImprocedenteModel;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\ExpedienteModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class EloquentExpedienteRepository implements ExpedienteRepositoryInterface
{
    /**
     * @return array<int, Expediente>
     */
    public function listar(): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ExpedienteModel> $models */
        $models = ExpedienteModel::query()->orderBy('id', 'desc')->get();
        return $models->map(fn (ExpedienteModel $m): Expediente => $this->toEntity($m))->all();
    }

    /**
     * @return array{items: array<int, Expediente>, total: int}
     */
    public function paginar(int $pagina = 1, int $porPagina = 15): array
    {
        $query = ExpedienteModel::query()->orderBy('id', 'desc');
        $total = $query->count();
        /** @var \Illuminate\Database\Eloquent\Collection<int, ExpedienteModel> $models */
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
        $model = ExpedienteModel::with('autorizacionesImprocedentes')->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function crear(Expediente $expediente): Expediente
    {
        $model = new ExpedienteModel();
        $model->uuid = $expediente->uuid;
        $model->numero_expediente = $expediente->numeroExpediente;
        $model->fecha_recepcion = $expediente->fechaRecepcion->format('Y-m-d H:i:s');
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
        $model->fecha_digitacion_ampliacion = $expediente->fechaDigitacionAmpliacion?->format('Y-m-d H:i:s');
        $model->usuario_id = $expediente->usuarioId;
        $model->fecha_digitacion = $expediente->fechaDigitacion->format('Y-m-d H:i:s');
        $model->save();

        return $this->toEntity($model);
    }

    public function actualizar(Expediente $expediente): Expediente
    {
        /** @var ExpedienteModel $model */
        $model = ExpedienteModel::findOrFail($expediente->id);
        $model->numero_expediente = $expediente->numeroExpediente;
        $model->fecha_recepcion = $expediente->fechaRecepcion->format('Y-m-d H:i:s');
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
        $model->fecha_digitacion_ampliacion = $expediente->fechaDigitacionAmpliacion?->format('Y-m-d H:i:s');
        $model->save();

        return $this->toEntity($model);
    }

    public function eliminar(int $id): void
    {
        ExpedienteModel::destroy($id);
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
        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPendientes(): array
    {
        return [];
    }

    /**
     * @return array<int, Expediente>
     */
    public function consultaRapida(?int $numeroExpediente): array
    {
        return [];
    }

    private function toDateTimeImmutable(\DateTimeInterface|string $date): DateTimeImmutable
    {
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }
        if ($date instanceof \DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($date);
        }
        return new DateTimeImmutable($date);
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
        );
    }
}
