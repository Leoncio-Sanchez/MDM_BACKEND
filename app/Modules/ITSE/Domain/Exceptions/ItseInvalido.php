<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Exceptions;

use DomainException;

final class ItseInvalido extends DomainException
{
    public static function titularNoExiste(int $id): self
    {
        return new self("El titular con ID {$id} no existe.");
    }
    
    public static function conductorNoExiste(int $id): self
    {
        return new self("El conductor con ID {$id} no existe.");
    }
    
    public static function nivelRiesgoNoExiste(int $id): self
    {
        return new self("El nivel de riesgo con ID {$id} no existe.");
    }
    
    public static function tipoInvalido(int $id): self
    {
        return new self("El tipo de ITSE {$id} no es válido.");
    }

    public static function numeroDuplicado(int $numero): self
    {
        return new self("El número de ITSE {$numero} ya existe.");
    }

    public static function reciboDuplicado(string $recibo): self
    {
        return new self("El recibo de pago {$recibo} ya existe.");
    }

    public static function yaInactivo(int $id): self
    {
        return new self("El ITSE {$id} ya tiene un estado inactivo registrado.");
    }
}
