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
}
