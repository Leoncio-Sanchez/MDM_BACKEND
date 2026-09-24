<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Infrastructure\Persistence\Repositories;

use App\Modules\UnidadesOrganicas\Domain\Contracts\UnidadOrganicaRepositoryInterface;
use App\Modules\UnidadesOrganicas\Infrastructure\Persistence\Models\UnidadOrganicaModel;

final class EloquentUnidadOrganicaRepository implements UnidadOrganicaRepositoryInterface
{
    /**
     * @return array<int, UnidadOrganicaModel>
     */
    public function listar(): array
    {
        return UnidadOrganicaModel::orderBy('nombre', 'asc')->get()->all();
    }
}
