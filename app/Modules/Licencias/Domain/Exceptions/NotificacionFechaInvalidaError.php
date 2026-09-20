<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Exceptions;

final class NotificacionFechaInvalidaError extends \DomainException
{
    public static function porFecha(string $fechaNotif, string $fechaEmision): self
    {
        return new self("La fecha de notificación ({$fechaNotif}) no puede ser anterior a la fecha de emisión ({$fechaEmision}).");
    }
}
