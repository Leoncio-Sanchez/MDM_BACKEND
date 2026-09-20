<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;

final readonly class EliminarZonificacionUseCase
{
    public function __construct(private ZonificacionRepositoryInterface $repository) {}

    public function execute(int $id): void
    {
        $this->repository->eliminar($id);
    }
}
