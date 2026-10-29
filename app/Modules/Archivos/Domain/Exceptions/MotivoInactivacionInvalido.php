<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Exceptions;

final class MotivoInactivacionInvalido extends ArchivoError
{
    public static function largo(int $minimo, int $maximo): self
    {
        return new self("El motivo de la inactivación debe tener entre {$minimo} y {$maximo} caracteres.");
    }
}
