<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\Exceptions;

final class UsuarioNoEncontrado extends SeguridadError
{
    public static function porUsername(string $username): self
    {
        return new self("Usuario '{$username}' no encontrado.");
    }

    public static function porId(int $id): self
    {
        return new self("Usuario con ID {$id} no encontrado.");
    }
}
