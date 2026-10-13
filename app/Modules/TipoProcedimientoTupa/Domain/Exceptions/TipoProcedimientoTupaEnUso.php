<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Domain\Exceptions;

class TipoProcedimientoTupaEnUso extends \DomainException
{
    public function __construct(int $cantidadExpedientes)
    {
        parent::__construct(
            "No se puede eliminar: el procedimiento está asociado a {$cantidadExpedientes} expediente(s). "
            . 'Desactívelo para que no se use en nuevos trámites.'
        );
    }
}
