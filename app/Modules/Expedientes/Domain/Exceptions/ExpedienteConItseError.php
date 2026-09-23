<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use DomainException;

final class ExpedienteConItseError extends DomainException
{
    public static function porId(int $id): self
    {
        return new self("No se puede eliminar el expediente {$id}: tiene una ITSE emitida.");
    }
}
