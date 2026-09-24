<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Application\UseCases;

use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;

final readonly class ListarUsuariosUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    /**
     * @return array<int, UsuarioModel>
     */
    public function execute(?string $busqueda = null): array
    {
        return $this->usuarioRepository->listar($busqueda);
    }
}
