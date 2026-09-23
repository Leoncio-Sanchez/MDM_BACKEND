<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use RuntimeException;

final class LicenciaYaEmitidaError extends RuntimeException
{
    public static function paraExpediente(int $expedienteId): self
    {
        return new self("El expediente {$expedienteId} ya tiene una licencia de funcionamiento emitida. Para denegarla primero debe eliminar la licencia.");
    }
}
