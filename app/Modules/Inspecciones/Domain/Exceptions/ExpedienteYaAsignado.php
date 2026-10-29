<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\Exceptions;

final class ExpedienteYaAsignado extends \DomainException
{
    public static function numero(int $numero): self
    {
        return new self("El expediente N° {$numero} ya tiene un inspector asignado. Use \"Reasignar\" para cambiarlo.");
    }
}
