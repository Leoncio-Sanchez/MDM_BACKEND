<?php

declare(strict_types=1);

namespace App\Modules\Pide\Domain\Exceptions;

final class SunatError extends PideError
{
    public static function timeout(): self
    {
        return new self('El servicio SUNAT no respondió en el tiempo esperado.');
    }

    public static function conexionFallida(): self
    {
        return new self('No se pudo conectar con el servicio SUNAT.');
    }

    public static function httpError(string $detalle): self
    {
        return new self("Error HTTP del servicio SUNAT: {$detalle}");
    }

    public static function inesperado(string $detalle): self
    {
        return new self("Error inesperado al consultar SUNAT: {$detalle}");
    }
}
