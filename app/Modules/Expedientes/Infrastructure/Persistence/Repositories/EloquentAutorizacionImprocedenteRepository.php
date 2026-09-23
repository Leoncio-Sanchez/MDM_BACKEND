<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Repositories;

use App\Modules\Expedientes\Domain\Contracts\AutorizacionImprocedenteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\AutorizacionImprocedenteModel;
use DateTimeImmutable;

final readonly class EloquentAutorizacionImprocedenteRepository implements AutorizacionImprocedenteRepositoryInterface
{
    public function buscarPorExpedienteYTipo(int $expedienteId, TipoAutorizacion $tipo): ?AutorizacionImprocedente
    {
        /** @var AutorizacionImprocedenteModel|null $model */
        $model = AutorizacionImprocedenteModel::query()
            ->where('expediente_id', $expedienteId)
            ->where('tipo_autorizacion', $tipo->value)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function crear(AutorizacionImprocedente $autorizacion): AutorizacionImprocedente
    {
        $model = new AutorizacionImprocedenteModel();
        $model->expediente_id = $autorizacion->expedienteId;
        $model->tipo_autorizacion = $autorizacion->tipoAutorizacion->value;
        $model->fecha_rechazo = $autorizacion->fechaRechazo->format('Y-m-d H:i:s');
        $model->documento = $autorizacion->documento;
        $model->observaciones = $autorizacion->observaciones;
        $model->usuario_id = $autorizacion->usuarioId;
        $model->fecha_digitacion = $autorizacion->fechaDigitacion->format('Y-m-d H:i:s');
        $model->save();

        return $this->toEntity($model);
    }

    public function existe(int $expedienteId, TipoAutorizacion $tipo): bool
    {
        return AutorizacionImprocedenteModel::query()
            ->where('expediente_id', $expedienteId)
            ->where('tipo_autorizacion', $tipo->value)
            ->exists();
    }

    private function toEntity(AutorizacionImprocedenteModel $model): AutorizacionImprocedente
    {
        $fechaRechazo = $model->fecha_rechazo instanceof DateTimeImmutable
            ? $model->fecha_rechazo
            : ($model->fecha_rechazo instanceof \DateTimeInterface
                ? DateTimeImmutable::createFromInterface($model->fecha_rechazo)
                : new DateTimeImmutable((string) $model->fecha_rechazo));

        $fechaDigitacion = $model->fecha_digitacion instanceof DateTimeImmutable
            ? $model->fecha_digitacion
            : ($model->fecha_digitacion instanceof \DateTimeInterface
                ? DateTimeImmutable::createFromInterface($model->fecha_digitacion)
                : new DateTimeImmutable((string) $model->fecha_digitacion));

        return new AutorizacionImprocedente(
            id: (int) $model->id,
            expedienteId: (int) $model->expediente_id,
            tipoAutorizacion: TipoAutorizacion::from((string) $model->tipo_autorizacion),
            fechaRechazo: $fechaRechazo,
            documento: $model->documento !== null ? (string) $model->documento : null,
            observaciones: $model->observaciones !== null ? (string) $model->observaciones : null,
            usuarioId: (int) $model->usuario_id,
            fechaDigitacion: $fechaDigitacion,
        );
    }
}
