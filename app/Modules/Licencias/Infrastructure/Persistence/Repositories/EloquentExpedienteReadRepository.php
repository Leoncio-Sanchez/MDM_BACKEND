<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Infrastructure\Persistence\Models\ExpedienteReadModel;
final readonly class EloquentExpedienteReadRepository implements ExpedienteReadRepositoryInterface {

    public function buscarPorId(int $id): ?object { return ExpedienteReadModel::find($id); }
    public function buscarPorNumeroYAnio(int $numero, int $anio): ?object {
        return ExpedienteReadModel::query()->where('numero', $numero)->whereYear('fecha_ingreso', $anio)->first();
    }
}
