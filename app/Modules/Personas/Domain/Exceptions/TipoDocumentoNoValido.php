<?php
declare(strict_types=1);

namespace App\Modules\Personas\Domain\Exceptions;

class TipoDocumentoNoValido extends \DomainException
{
    public static function crear(): self
    {
        return new self("El tipo de documento de identidad no es válido.");
    }
}
