<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;
use App\Modules\NivelRiesgo\Domain\Exceptions\NivelRiesgoNoEncontrado;

final readonly class ObtenerNivelRiesgoUseCase
{
    public function __construct(private NivelRiesgoRepositoryInterface $repository) {}

    public function execute(int $id): NivelRiesgo
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new NivelRiesgoNoEncontrado;
        }

        return $entity;
    }
}
