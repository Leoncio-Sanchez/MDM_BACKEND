<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Exceptions;

use DomainException;

final class ExpedienteNoValidoItse extends DomainException
{
    public static function noExiste(int $id): self
    {
        return new self("El expediente con ID {$id} no existe.");
    }

    public static function anulado(int $id): self
    {
        return new self("El expediente con ID {$id} está anulado: no se puede emitir un certificado ITSE sobre él.");
    }
}
