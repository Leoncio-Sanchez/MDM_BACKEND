<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Application\UseCases;

use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;

final readonly class EliminarUsuarioUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    public function execute(int $id): bool
    {
        return $this->usuarioRepository->desactivar($id);
    }
}
