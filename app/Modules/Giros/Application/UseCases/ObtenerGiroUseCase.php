<?php

declare(strict_types=1);

namespace App\Modules\Giros\Application\UseCases;

use App\Modules\Giros\Domain\Contracts\GiroRepositoryInterface;
use App\Modules\Giros\Domain\Entities\Giro;
use App\Modules\Giros\Domain\Exceptions\GiroNoEncontrado;

final readonly class ObtenerGiroUseCase
{
    public function __construct(private GiroRepositoryInterface $repository) {}

    public function execute(int $id): Giro
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new GiroNoEncontrado;
        }

        return $entity;
    }
}
