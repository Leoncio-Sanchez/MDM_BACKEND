<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Application\UseCases;

use App\Modules\Inspectores\Domain\Contracts\InspectorRepositoryInterface;
use App\Models\Usuario as UsuarioModel;

final readonly class ListarInspectoresUseCase
{
    public function __construct(
        private InspectorRepositoryInterface $repository,
    ) {}

    /**
     * @return array<int, UsuarioModel>
     */
    public function execute(?string $busqueda = null): array
    {
        return $this->repository->listar($busqueda);
    }
}
