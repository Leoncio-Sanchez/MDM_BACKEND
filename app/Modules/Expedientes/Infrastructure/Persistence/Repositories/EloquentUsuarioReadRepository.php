<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Repositories;

use App\Modules\Expedientes\Domain\Contracts\UsuarioReadRepositoryInterface;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\UsuarioReadModel;

final readonly class EloquentUsuarioReadRepository implements UsuarioReadRepositoryInterface
{
    public function buscarPorId(int $id): ?object
    {
        return UsuarioReadModel::find($id);
    }

    public function existe(int $id): bool
    {
        return UsuarioReadModel::query()->where('id', $id)->exists();
    }
}
