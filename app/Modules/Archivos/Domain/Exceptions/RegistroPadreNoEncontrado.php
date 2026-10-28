<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Exceptions;

/** Se intentó adjuntar un archivo a un expediente, licencia o ITSE que no existe. */
final class RegistroPadreNoEncontrado extends ArchivoError
{
    public static function conId(int $id): self
    {
        return new self("No existe el registro #{$id} al que se quiere adjuntar el archivo.");
    }
}
