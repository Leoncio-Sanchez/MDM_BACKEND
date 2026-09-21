<?php

declare(strict_types=1);

namespace App\Modules\Personas\Domain\Exceptions;

class ReniecRespuestaInvalida extends \DomainException
{
    public static function porDni(string $dni): self
    {
        return new self("Respuesta inválida de RENIEC para el DNI {$dni}.");
    }
}
