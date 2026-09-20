<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Exceptions;

final class LicenciaNoEncontrada extends \DomainException
{
    public static function porId(int $id): self
    {
        return new self("Licencia con ID {$id} no encontrada");
    }
}
