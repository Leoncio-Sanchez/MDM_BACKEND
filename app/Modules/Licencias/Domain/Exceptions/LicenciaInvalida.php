<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Exceptions;

final class LicenciaInvalida extends \DomainException
{
    public static function razon(string $razon): self
    {
        return new self($razon);
    }
}
