<?php

declare(strict_types=1);

namespace App\Exceptions\Seguridad;

final class CredencialesInvalidas extends SeguridadError
{
    public static function default(): self
    {
        return new self('La combinación de credenciales no tiene una cuenta activa');
    }
}
