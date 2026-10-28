<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Domain\Exceptions;

use DomainException;

/**
 * El mismo mensaje sirve si el número no existe o si el documento no coincide:
 * así no se puede averiguar qué números existen probando documentos al azar.
 */
final class TramiteNoEncontrado extends DomainException
{
    public static function conEsosDatos(): self
    {
        return new self('No encontramos un trámite con ese número y documento. Verifique los datos ingresados.');
    }
}
