<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Domain\Entities\Zonificacion;
use App\Modules\Zonificacion\Domain\Exceptions\ZonificacionNoEncontrado;

final readonly class ObtenerZonificacionUseCase
{
    public function __construct(private ZonificacionRepositoryInterface $repository) {}

    public function execute(int $id): Zonificacion
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new ZonificacionNoEncontrado;
        }

        return $entity;
    }
}
