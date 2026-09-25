<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Application\UseCases;

use App\Modules\Inspectores\Domain\Contracts\InspectorRepositoryInterface;
use App\Models\Usuario as UsuarioModel;

final readonly class ActualizarInspectorUseCase
{
    public function __construct(
        private InspectorRepositoryInterface $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function execute(int $id, array $datos): ?UsuarioModel
    {
        return $this->repository->actualizar($id, $datos);
    }
}
