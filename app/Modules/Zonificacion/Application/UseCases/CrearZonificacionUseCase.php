<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Application\DTOs\GuardarZonificacionDTO;
use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Domain\Entities\Zonificacion;

final readonly class CrearZonificacionUseCase
{
    public function __construct(private ZonificacionRepositoryInterface $repository) {}

    public function execute(GuardarZonificacionDTO $dto): Zonificacion
    {
        $entity = Zonificacion::crear($dto->codigo, $dto->nombre, $dto->estaActivo, $dto->usuarioId);

        return $this->repository->guardar($entity);
    }
}
