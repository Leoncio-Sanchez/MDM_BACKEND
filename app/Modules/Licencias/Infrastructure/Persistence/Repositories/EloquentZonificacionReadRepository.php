<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;
use App\Modules\Licencias\Domain\Contracts\ZonificacionReadRepositoryInterface;
use App\Modules\Licencias\Infrastructure\Persistence\Models\ZonificacionReadModel;
final readonly class EloquentZonificacionReadRepository implements ZonificacionReadRepositoryInterface {
    public function buscarPorId(int $id): ?object { return ZonificacionReadModel::find($id); }
}
