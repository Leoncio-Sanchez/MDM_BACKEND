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

    // Sin detalle técnico en el mensaje: se registra en el log del servidor.

    public static function httpError(): self
    {
        return new self('El servicio SUNAT respondió con un error. Intente nuevamente en unos minutos.');
    }

    public static function inesperado(): self
    {
        return new self('Ocurrió un error al consultar SUNAT. Intente nuevamente en unos minutos.');
    }
}
