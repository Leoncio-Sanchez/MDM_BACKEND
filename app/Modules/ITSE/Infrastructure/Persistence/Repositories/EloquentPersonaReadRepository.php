<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Models\PersonaReadModel;

final class EloquentPersonaReadRepository implements PersonaReadRepositoryInterface
{
    public function existe(int $id): bool
    {
        return PersonaReadModel::where('id', $id)->exists();
    }

    public function buscarPorId(int $id): ?\stdClass
    {
        $model = PersonaReadModel::find($id);
        return $model ? (object) $model->toArray() : null;
    }
}
