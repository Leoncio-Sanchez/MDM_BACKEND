<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Application\UseCases;

use App\Modules\Inspectores\Domain\Contracts\InspectorRepositoryInterface;

final readonly class EliminarInspectorUseCase
{
    public function __construct(
        private InspectorRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->eliminar($id);
    }
}
