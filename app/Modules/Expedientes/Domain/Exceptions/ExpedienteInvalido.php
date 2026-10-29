<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use DomainException;

final class ExpedienteInvalido extends DomainException
{
    public static function numeroInvalido(int $numero): self
    {
        return new self("El número de expediente {$numero} no es válido. Debe ser mayor a 0.");
    }

    public static function numeroDuplicado(int $numero, int $anio): self
    {
        return new self("Ya existe un expediente con el número {$numero} en el año {$anio}.");
    }

    public static function motivoAnulacion(int $minimo, int $maximo): self
    {
        return new self("El motivo de la anulación debe tener entre {$minimo} y {$maximo} caracteres.");
    }
}
