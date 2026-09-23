<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use DomainException;

final class TipoProcedimientoNoValido extends DomainException
{
    public static function noExiste(int $id): self
    {
        return new self("El tipo de procedimiento TUPA con ID {$id} no existe o no está activo.");
    }
}
