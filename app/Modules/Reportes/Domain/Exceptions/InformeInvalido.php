<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\Exceptions;

use DomainException;

final class InformeInvalido extends DomainException
{
    public static function noEsMesCompleto(): self
    {
        return new self('El informe formal es mensual: elija un mes completo (del primer al último día).');
    }

    public static function mesFuturo(): self
    {
        return new self('No se puede emitir el informe de un mes que todavía no empieza.');
    }
}
