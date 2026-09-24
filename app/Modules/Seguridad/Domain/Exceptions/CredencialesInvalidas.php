<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\Exceptions;

final class CredencialesInvalidas extends SeguridadError
{
    public static function default(): self
    {
        // MENSAJE EXACTO DE DJANGO
        return new self('La combinación de credenciales no tiene una cuenta activa');
    }
}
