<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use DomainException;

final class ExpedienteDuplicadoError extends DomainException
{
    public static function porNumeroYAnio(int $numero, int $anio): self
    {
        return new self("Ya existe un expediente con número {$numero} en el año {$anio}.");
    }
}
