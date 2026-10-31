<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Exceptions;

final class LicenciaDenegadaError extends \DomainException
{
    public static function porExpediente(int $expedienteId): self
    {
        return new self("El expediente con ID {$expedienteId} tiene registrada la denegatoria de la licencia: no se puede emitir una licencia sobre él.");
    }
}
