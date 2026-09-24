<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Application\UseCases;

use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;

final readonly class ActualizarUsuarioUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>|null  $perfilDatos
     */
    public function execute(int $id, array $datos, ?array $perfilDatos = null, ?int $digitadorId = null): ?UsuarioModel
    {
        return $this->usuarioRepository->actualizar($id, $datos, $perfilDatos, $digitadorId);
    }
}
