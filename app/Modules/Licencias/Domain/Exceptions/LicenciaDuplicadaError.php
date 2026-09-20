<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Exceptions;

final class LicenciaDuplicadaError extends \DomainException
{
    public static function porNumero(string $numero): self
    {
        return new self("Ya existe una licencia con el número {$numero}.");
    }
}
