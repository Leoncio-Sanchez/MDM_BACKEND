<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Exceptions;

final class ArchivoYaInactivo extends ArchivoError
{
    public static function nombre(string $nombreOriginal): self
    {
        return new self("El archivo «{$nombreOriginal}» ya está inactivo.");
    }
}
