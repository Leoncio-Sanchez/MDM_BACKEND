<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use DomainException;

final class ExpedienteConLicenciaError extends DomainException
{
    public static function porNumero(int $numero): self
    {
        return new self("No se puede anular el expediente N° {$numero}: tiene una licencia de funcionamiento emitida.");
    }
}
