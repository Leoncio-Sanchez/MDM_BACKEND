<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Application\UseCases;

use App\Modules\Inspectores\Domain\Contracts\InspectorRepositoryInterface;

/** Reemplaza al antiguo borrado: la cuenta del inspector queda inactiva y su historial se conserva. */
final readonly class DesactivarInspectorUseCase
{
    public function __construct(
        private InspectorRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->desactivar($id);
    }
}
