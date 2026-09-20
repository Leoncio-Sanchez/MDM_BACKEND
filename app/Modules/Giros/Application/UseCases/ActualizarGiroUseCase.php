<?php

declare(strict_types=1);

namespace App\Modules\Giros\Application\UseCases;

use App\Modules\Giros\Application\DTOs\ActualizarGiroDTO;
use App\Modules\Giros\Domain\Contracts\GiroRepositoryInterface;
use App\Modules\Giros\Domain\Entities\Giro;
use App\Modules\Giros\Domain\Exceptions\GiroNoEncontrado;

final readonly class ActualizarGiroUseCase
{
    public function __construct(private GiroRepositoryInterface $repository) {}

    public function execute(int $id, ActualizarGiroDTO $dto): Giro
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new GiroNoEncontrado;
        }
        $updated = $entity->actualizar($dto->ciiuId, $dto->nombre, $dto->estaActivo, $dto->usuarioId);

        return $this->repository->actualizar($updated);
    }
}
