<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Domain\Exceptions;

final class UnidadOrganicaEnUso extends \DomainException
{
    public static function porDependencias(int $procedimientos, int $usuarios): self
    {
        $detalle = array_filter([
            $procedimientos > 0 ? "{$procedimientos} procedimiento(s) TUPA" : null,
            $usuarios > 0 ? "{$usuarios} usuario(s)" : null,
        ]);

        return new self(
            'No se puede eliminar: la unidad está asociada a ' . implode(' y ', $detalle) . '. '
            . 'Desactívela para que no se use en registros nuevos.'
        );
    }
}
