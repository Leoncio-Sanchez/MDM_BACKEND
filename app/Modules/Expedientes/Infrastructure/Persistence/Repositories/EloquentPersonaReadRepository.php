<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Repositories;

use App\Modules\Expedientes\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\PersonaReadModel;

final readonly class EloquentPersonaReadRepository implements PersonaReadRepositoryInterface
{
    public function buscarPorId(int $id): ?object
    {
        return PersonaReadModel::find($id);
    }

    public function existe(int $id): bool
    {
        return PersonaReadModel::query()->where('id', $id)->exists();
    }
}
