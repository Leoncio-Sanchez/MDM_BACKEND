<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Application\DTOs\GuardarZonificacionDTO;
use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Domain\Entities\Zonificacion;
use App\Modules\Zonificacion\Domain\Exceptions\ZonificacionNoEncontrado;

final readonly class ActualizarZonificacionUseCase
{
    public function __construct(private ZonificacionRepositoryInterface $repository) {}

    public function execute(int $id, GuardarZonificacionDTO $dto): Zonificacion
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new ZonificacionNoEncontrado;
        }
        $updated = $entity->actualizar($dto->codigo, $dto->nombre, $dto->estaActivo, $dto->usuarioId);

        return $this->repository->actualizar($updated);
    }
}
