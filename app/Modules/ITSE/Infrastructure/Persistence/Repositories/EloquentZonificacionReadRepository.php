<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\ZonificacionReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Models\ZonificacionReadModel;

final class EloquentZonificacionReadRepository implements ZonificacionReadRepositoryInterface
{
    public function existe(int $id): bool
    {
        return ZonificacionReadModel::where('id', $id)->exists();
    }
}
