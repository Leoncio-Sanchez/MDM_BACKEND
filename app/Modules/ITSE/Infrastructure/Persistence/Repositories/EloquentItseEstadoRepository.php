<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\ItseEstadoRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\ItseEstado;
use App\Modules\ITSE\Infrastructure\Persistence\Models\ItseEstadoModel;
use Carbon\CarbonImmutable;

final class EloquentItseEstadoRepository implements ItseEstadoRepositoryInterface
{
    public function crear(ItseEstado $estado): ItseEstado
    {
        $model = ItseEstadoModel::create([
            'itse_id' => $estado->itseId,
            'estado' => $estado->estadoId,
            'fecha' => $estado->fechaEstado->format('Y-m-d'),
            'observaciones' => $estado->observaciones,
            'usuario_id' => $estado->usuarioId,
        ]);

        return new ItseEstado(
            id: $model->id,
            fechaEstado: CarbonImmutable::parse($model->fecha),
            documento: '',
            observaciones: (string) $model->observaciones,
            fechaDigitacion: CarbonImmutable::now(), // mock
            estadoId: (int) $model->estado,
            itseId: $model->itse_id,
            usuarioId: $model->usuario_id,
        );
    }

    public function tieneEstado(int $itseId, string $estadoNombre): bool
    {
        return ItseEstadoModel::where('itse_id', $itseId)
            ->where('estado', $estadoNombre)
            ->exists();
    }

    public function insertar(int $itseId, string $estado, string $fecha, string $observaciones, int $usuarioId): void
    {
        ItseEstadoModel::create([
            'itse_id' => $itseId,
            'estado' => $estado,
            'fecha' => $fecha,
            'observaciones' => $observaciones,
            'usuario_id' => $usuarioId,
        ]);
    }
}
