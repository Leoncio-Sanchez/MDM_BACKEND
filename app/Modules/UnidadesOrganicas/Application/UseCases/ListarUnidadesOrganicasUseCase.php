<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Application\UseCases;

use App\Modules\UnidadesOrganicas\Domain\Contracts\UnidadOrganicaRepositoryInterface;
use App\Modules\UnidadesOrganicas\Domain\Entities\UnidadOrganica;

final readonly class ListarUnidadesOrganicasUseCase
{
    public function __construct(
        private UnidadOrganicaRepositoryInterface $repository
    ) {}

    /** @return array<int, UnidadOrganica> */
    public function execute(): array
    {
        return $this->repository->listar();
    }
}
