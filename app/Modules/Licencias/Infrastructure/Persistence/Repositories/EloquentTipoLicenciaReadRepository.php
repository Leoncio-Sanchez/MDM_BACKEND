<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;
use App\Modules\Licencias\Domain\Contracts\TipoLicenciaReadRepositoryInterface;
use App\Modules\Licencias\Infrastructure\Persistence\Models\TipoLicenciaReadModel;
final readonly class EloquentTipoLicenciaReadRepository implements TipoLicenciaReadRepositoryInterface {
    public function buscarPorId(int $id): ?object { return TipoLicenciaReadModel::find($id); }
}
