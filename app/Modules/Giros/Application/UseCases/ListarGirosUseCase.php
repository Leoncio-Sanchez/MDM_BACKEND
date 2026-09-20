<?php

declare(strict_types=1);

namespace App\Modules\Giros\Application\UseCases;

use App\Modules\Giros\Domain\Contracts\GiroRepositoryInterface;
use App\Modules\Giros\Domain\Entities\Giro;

final readonly class ListarGirosUseCase
{
    public function __construct(private GiroRepositoryInterface $repository) {}

    /** @return Giro[] */
    public function execute(): array
    {
        return $this->repository->obtenerTodos();
    }
}
