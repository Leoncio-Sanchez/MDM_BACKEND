<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use RuntimeException;

final class ExpedienteNoEncontrado extends RuntimeException
{
    public static function porId(int $id): self
    {
        return new self("El expediente con ID {$id} no existe.");
    }

    public static function porNumero(int $numero): self
    {
        return new self("El expediente con número {$numero} no existe.");
    }
}
