<?php

declare(strict_types=1);

namespace App\Modules\Giros\Application\UseCases;

use App\Modules\Giros\Application\DTOs\CrearGiroDTO;
use App\Modules\Giros\Domain\Contracts\GiroRepositoryInterface;
use App\Modules\Giros\Domain\Entities\Giro;

final readonly class CrearGiroUseCase
{
    public function __construct(private GiroRepositoryInterface $repository) {}

    public function execute(CrearGiroDTO $dto): Giro
    {
        $entity = Giro::crear($dto->ciiuId, $dto->nombre, $dto->estaActivo, $dto->usuarioId);

        return $this->repository->guardar($entity);
    }
}
