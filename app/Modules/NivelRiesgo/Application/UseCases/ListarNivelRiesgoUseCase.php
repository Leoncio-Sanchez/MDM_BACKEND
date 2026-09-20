<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;

final readonly class ListarNivelRiesgoUseCase
{
    public function __construct(private NivelRiesgoRepositoryInterface $repository) {}

    /**
     * @return \App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo[]
     */
    public function execute(): array
    {
        return $this->repository->obtenerTodos();
    }
}
