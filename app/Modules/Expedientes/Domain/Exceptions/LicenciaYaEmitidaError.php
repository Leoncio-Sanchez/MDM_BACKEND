<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use RuntimeException;

final class LicenciaYaEmitidaError extends RuntimeException
{
    public static function paraExpediente(int $numeroExpediente): self
    {
        return new self("El expediente N° {$numeroExpediente} ya tiene su licencia de funcionamiento emitida: no se puede denegar.");
    }
}
