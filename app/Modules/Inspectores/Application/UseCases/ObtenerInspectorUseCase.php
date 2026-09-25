<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Application\UseCases;

use App\Modules\Inspectores\Domain\Contracts\InspectorRepositoryInterface;
use App\Models\Usuario as UsuarioModel;

final readonly class ObtenerInspectorUseCase
{
    public function __construct(
        private InspectorRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?UsuarioModel
    {
        return $this->repository->buscarPorId($id);
    }
}
