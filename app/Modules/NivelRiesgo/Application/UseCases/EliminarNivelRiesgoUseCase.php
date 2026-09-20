<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;

final readonly class EliminarNivelRiesgoUseCase
{
    public function __construct(private NivelRiesgoRepositoryInterface $repository) {}

    public function execute(int $id): void
    {
        $this->repository->eliminar($id);
    }
}
