<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Exceptions;

final class ErrorAlGuardarArchivo extends ArchivoError
{
    public static function porFallo(string $detalle): self
    {
        return new self("Error al guardar el archivo: {$detalle}");
    }
}
