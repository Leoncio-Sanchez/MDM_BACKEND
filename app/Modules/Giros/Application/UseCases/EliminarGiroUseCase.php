<?php

declare(strict_types=1);

namespace App\Modules\Giros\Application\UseCases;

use App\Modules\Giros\Domain\Contracts\GiroRepositoryInterface;

final readonly class EliminarGiroUseCase
{
    public function __construct(private GiroRepositoryInterface $repository) {}

    public function execute(int $id): void
    {
        $this->repository->eliminar($id);
    }
}
