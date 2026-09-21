<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Exceptions;

use RuntimeException;

final class ItseNoEncontrado extends RuntimeException
{
    public static function porId(int $id): self
    {
        return new self("El ITSE con ID {$id} no existe.");
    }
}
