<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Application\UseCases;

use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;

final readonly class CrearUsuarioUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
        private PasswordHasherInterface $hasher,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>|null  $perfilDatos
     */
    public function execute(array $datos, ?array $perfilDatos = null, ?int $digitadorId = null): UsuarioModel
    {
        if (! empty($datos['password'])) {
            $datos['password'] = $this->hasher->hash((string) $datos['password']);
        }

        return $this->usuarioRepository->crear($datos, $perfilDatos, $digitadorId);
    }
}
