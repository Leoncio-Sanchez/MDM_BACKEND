<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Domain\Exceptions;

class TipoProcedimientoTupaNoEncontrado extends \DomainException
{
    public function __construct()
    {
        parent::__construct('TipoProcedimientoTupa no encontrado');
    }
}
