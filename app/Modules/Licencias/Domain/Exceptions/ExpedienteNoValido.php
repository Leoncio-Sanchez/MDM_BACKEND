<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Exceptions;

final class ExpedienteNoValido extends \DomainException
{
    public static function porId(int $id): self
    {
        return new self("Expediente con ID {$id} no es válido");
    }

    public static function anulado(int $id): self
    {
        return new self("El expediente con ID {$id} está anulado: no se puede emitir una licencia sobre él.");
    }
}
