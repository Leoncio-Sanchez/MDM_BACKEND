<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use RuntimeException;

final class ItseYaEmitidaError extends RuntimeException
{
    public static function paraExpediente(int $expedienteId): self
    {
        return new self("El expediente {$expedienteId} ya tiene una ITSE emitida. Para declararla desfavorable primero debe eliminar la ITSE.");
    }
}
