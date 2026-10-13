<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Domain\Exceptions;

final class UnidadOrganicaEnUso extends \DomainException
{
    public static function porProcedimientos(int $procedimientos): self
    {
        return new self(
            "No se puede eliminar: la unidad está asociada a {$procedimientos} procedimiento(s) TUPA. "
            . 'Desactívela para que no se use en registros nuevos.'
        );
    }
}
