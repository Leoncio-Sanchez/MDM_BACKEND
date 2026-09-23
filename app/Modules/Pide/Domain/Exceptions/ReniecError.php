<?php

declare(strict_types=1);

namespace App\Modules\Pide\Domain\Exceptions;

final class ReniecError extends PideError
{
    public static function credencialesNoConfiguradas(): self
    {
        return new self(
            'Las credenciales RENIEC no están configuradas '
            .'(RENIEC_NUDNIUSUARIO, RENIEC_NURUCUSUARIO, RENIEC_PASSWORD).'
        );
    }

    public static function timeout(): self
    {
        return new self('El servicio RENIEC no respondió en el tiempo esperado.');
    }

    public static function conexionFallida(): self
    {
        return new self('No se pudo conectar con el servicio RENIEC.');
    }

    public static function httpError(string $detalle): self
    {
        return new self("Error HTTP del servicio RENIEC: {$detalle}");
    }

    public static function inesperado(string $detalle): self
    {
        return new self("Error inesperado al consultar RENIEC: {$detalle}");
    }
}
