<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use RuntimeException;

final class TipoAutorizacionInvalidoError extends RuntimeException
{
    public static function paraTipo(string $tipo): self
    {
        return new self("Tipo de autorización '{$tipo}' no válido. Opciones: LF, ITSE");
    }
}
