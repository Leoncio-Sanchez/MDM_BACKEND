<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Domain\Entities\Zonificacion;

final readonly class ListarZonificacionUseCase
{
    public function __construct(private ZonificacionRepositoryInterface $repository) {}

    /** @return Zonificacion[] */
    public function execute(): array
    {
        return $this->repository->obtenerTodos();
    }
}
