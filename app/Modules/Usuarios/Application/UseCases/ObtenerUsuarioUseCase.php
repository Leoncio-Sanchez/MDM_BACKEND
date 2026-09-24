<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Application\UseCases;

use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;

final readonly class ObtenerUsuarioUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    public function execute(int $id): ?UsuarioModel
    {
        return $this->usuarioRepository->buscarPorId($id);
    }
}
