<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Exceptions;

final class LicenciaTieneDependientesError extends \DomainException
{
    public static function porId(int $id): self
    {
        return new self("No se puede eliminar la licencia {$id}: tiene licencias dependientes asociadas.");
    }
}
