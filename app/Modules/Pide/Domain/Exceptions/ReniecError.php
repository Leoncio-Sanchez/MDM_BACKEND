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

    // Los mensajes no incluyen el detalle técnico: puede contener la URL con la contraseña
    // de PIDE. El detalle se registra en el log del servidor (con la contraseña oculta).

    public static function httpError(): self
    {
        return new self('El servicio RENIEC respondió con un error. Intente nuevamente en unos minutos.');
    }

    public static function inesperado(): self
    {
        return new self('Ocurrió un error al consultar RENIEC. Intente nuevamente en unos minutos.');
    }

    /** PIDE respondió, pero rechazó la consulta (credenciales vencidas, servicio no habilitado…). */
    public static function rechazada(string $codigo, string $descripcion): self
    {
        return new self(trim("RENIEC rechazó la consulta (código {$codigo}). {$descripcion}")
            .' Comuníquelo al área de sistemas.');
    }
}
