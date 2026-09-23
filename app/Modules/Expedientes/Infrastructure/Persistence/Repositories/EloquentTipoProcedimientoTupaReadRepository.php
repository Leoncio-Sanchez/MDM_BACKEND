<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Repositories;

use App\Modules\Expedientes\Domain\Contracts\TipoProcedimientoTupaReadRepositoryInterface;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\TipoProcedimientoTupaReadModel;

final readonly class EloquentTipoProcedimientoTupaReadRepository implements TipoProcedimientoTupaReadRepositoryInterface
{
    public function buscarPorId(int $id): ?object
    {
        return TipoProcedimientoTupaReadModel::find($id);
    }

    public function existe(int $id): bool
    {
        return TipoProcedimientoTupaReadModel::query()->where('id', $id)->exists();
    }
}
