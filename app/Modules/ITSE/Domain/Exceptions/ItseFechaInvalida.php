<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Exceptions;

use DomainException;

final class ItseFechaInvalida extends DomainException
{
    public static function caducidadAntesDeExpedicion(): self
    {
        return new self('La fecha de caducidad no puede ser anterior a la expedición.');
    }

    public static function notificacionAntesDeExpedicion(): self
    {
        return new self('La fecha de notificación no puede ser anterior a la expedición.');
    }
}
