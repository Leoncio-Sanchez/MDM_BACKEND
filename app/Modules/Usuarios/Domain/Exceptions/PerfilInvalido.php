<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Domain\Exceptions;

use DomainException;

final class PerfilInvalido extends DomainException
{
    public static function jefeSinItse(): self
    {
        return new self('El jefe de ITSE necesita también el acceso de ITSE.');
    }
}
