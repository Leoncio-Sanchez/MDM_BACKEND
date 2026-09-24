<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Domain\Contracts;

use App\Modules\UnidadesOrganicas\Infrastructure\Persistence\Models\UnidadOrganicaModel;

interface UnidadOrganicaRepositoryInterface
{
    /**
     * @return array<int, UnidadOrganicaModel>
     */
    public function listar(): array;
}
