<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\Exceptions;

final class ActaDuplicada extends \DomainException
{
    public static function numero(string $numero): self
    {
        return new self("Ya existe un acta con el número {$numero}.");
    }

    public static function visita(int $numeroExpediente): self
    {
        return new self("El acta de esta visita del expediente N° {$numeroExpediente} ya fue registrada.");
    }
}
