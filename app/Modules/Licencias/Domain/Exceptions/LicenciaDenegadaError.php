<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Exceptions;

final class LicenciaDenegadaError extends \DomainException
{
    public static function porExpediente(int $expedienteId): self
    {
        return new self("El expediente {$expedienteId} tiene una autorización improcedente de tipo LF.");
    }
}
