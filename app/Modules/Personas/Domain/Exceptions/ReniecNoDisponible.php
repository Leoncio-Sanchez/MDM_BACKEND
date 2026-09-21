<?php

declare(strict_types=1);

namespace App\Modules\Personas\Domain\Exceptions;

class ReniecNoDisponible extends \DomainException
{
    public static function porDni(string $dni): self
    {
        return new self("El servicio RENIEC no está disponible para el DNI {$dni}.");
    }
}
