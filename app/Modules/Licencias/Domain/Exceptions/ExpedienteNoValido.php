<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Exceptions;

final class ExpedienteNoValido extends \DomainException
{
    public static function porId(int $id): self
    {
        return new self("Expediente con ID {$id} no es válido");
    }
}
