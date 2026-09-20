<?php
declare(strict_types=1);

namespace App\Modules\Personas\Domain\Exceptions;

class DocumentoInvalido extends \DomainException
{
    public static function vacio(): self
    {
        return new self("El número de documento no puede estar vacío.");
    }
}
