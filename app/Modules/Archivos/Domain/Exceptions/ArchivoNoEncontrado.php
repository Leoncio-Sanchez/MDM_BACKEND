<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Exceptions;

final class ArchivoNoEncontrado extends ArchivoError
{
    public static function porId(int $id): self
    {
        return new self("El archivo con id {$id} no existe.");
    }

    public static function porUuid(string $uuid): self
    {
        return new self("El archivo con uuid {$uuid} no existe.");
    }
}
