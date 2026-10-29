<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use DomainException;

/** El expediente está anulado: queda solo como constancia, no admite más trámites. */
final class ExpedienteAnulado extends DomainException
{
    public static function yaAnulado(int $numero): self
    {
        return new self("El expediente N° {$numero} ya está anulado.");
    }

    public static function noAdmiteCambios(int $numero): self
    {
        return new self("El expediente N° {$numero} está anulado: no admite cambios ni nuevos trámites.");
    }
}
