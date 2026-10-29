<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use RuntimeException;

final class ItseYaEmitidaError extends RuntimeException
{
    public static function paraExpediente(int $numeroExpediente): self
    {
        return new self("El expediente N° {$numeroExpediente} ya tiene su certificado ITSE emitido: no se puede denegar.");
    }
}
