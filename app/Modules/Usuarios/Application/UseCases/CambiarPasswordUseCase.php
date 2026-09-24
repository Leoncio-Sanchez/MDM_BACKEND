<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Application\UseCases;

use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;

final readonly class CambiarPasswordUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
        private PasswordHasherInterface $hasher,
    ) {}

    /**
     * @return array{success: bool, status: int, data: array<string, string>}
     */
    public function execute(int $usuarioId, string $password, string $passwordConfirm): array
    {
        $password = trim($password);
        $passwordConfirm = trim($passwordConfirm);

        if ($password === '') {
            return [
                'success' => false,
                'status' => 400,
                'data' => ['error' => 'La contraseña no puede estar vacía.'],
            ];
        }

        if ($password !== $passwordConfirm) {
            return [
                'success' => false,
                'status' => 400,
                'data' => ['error' => 'Las contraseñas no coinciden.'],
            ];
        }

        if (mb_strlen($password) < 6) {
            return [
                'success' => false,
                'status' => 400,
                'data' => ['error' => 'La contraseña debe tener al menos 6 caracteres.'],
            ];
        }

        $user = $this->usuarioRepository->buscarPorId($usuarioId);
        if (! $user) {
            return [
                'success' => false,
                'status' => 404,
                'data' => ['error' => 'Usuario no encontrado.'],
            ];
        }

        $hashed = $this->hasher->hash($password);
        $this->usuarioRepository->actualizarPassword($usuarioId, $hashed);

        return [
            'success' => true,
            'status' => 200,
            'data' => ['mensaje' => 'Contraseña actualizada correctamente.'],
        ];
    }
}
