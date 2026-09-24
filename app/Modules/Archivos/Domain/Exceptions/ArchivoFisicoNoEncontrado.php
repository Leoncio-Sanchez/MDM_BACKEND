<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Exceptions;

final class ArchivoFisicoNoEncontrado extends ArchivoError
{
    public static function enRuta(string $ruta): self
    {
        return new self('El archivo físico no se encontró en el servidor.');
    }
}
