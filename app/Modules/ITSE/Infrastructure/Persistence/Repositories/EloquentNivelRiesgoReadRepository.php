<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\NivelRiesgoReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Models\NivelRiesgoReadModel;

final class EloquentNivelRiesgoReadRepository implements NivelRiesgoReadRepositoryInterface
{
    public function existe(int $id): bool
    {
        return NivelRiesgoReadModel::where('id', $id)->exists();
    }

    public function buscarPorId(int $id): ?\stdClass
    {
        $model = NivelRiesgoReadModel::find($id);
        return $model ? (object) $model->toArray() : null;
    }
}
