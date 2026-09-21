<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\AutorizacionImprocedenteReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Models\AutorizacionImprocedenteReadModel;

final class EloquentAutorizacionImprocedenteReadRepository implements AutorizacionImprocedenteReadRepositoryInterface
{
    public function existe(int $expedienteId, string $tipoAutorizacion): bool
    {
        return AutorizacionImprocedenteReadModel::where('expediente_id', $expedienteId)
            ->where('tipo_autorizacion', $tipoAutorizacion)
            ->exists();
    }
}
