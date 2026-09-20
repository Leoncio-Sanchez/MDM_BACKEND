<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;
use App\Modules\Licencias\Domain\Contracts\NivelRiesgoReadRepositoryInterface;
use App\Modules\Licencias\Infrastructure\Persistence\Models\NivelRiesgoReadModel;
final readonly class EloquentNivelRiesgoReadRepository implements NivelRiesgoReadRepositoryInterface {
    public function buscarPorId(int $id): ?object { return NivelRiesgoReadModel::find($id); }
}
