<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;
use App\Modules\Licencias\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\Licencias\Infrastructure\Persistence\Models\PersonaReadModel;
final readonly class EloquentPersonaReadRepository implements PersonaReadRepositoryInterface {

    public function buscarPorId(int $id): ?object { return PersonaReadModel::find($id); }
    public function buscarPorDocumento(string $documento): ?object {
        return PersonaReadModel::query()->where('numero_documento', $documento)->first();
    }
}
