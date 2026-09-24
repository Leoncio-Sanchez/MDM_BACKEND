<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\Exceptions;

final class TokenInvalido extends SeguridadError
{
    public static function default(): self
    {
        return new self('Token inválido o expirado.');
    }
}
