<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Domain\Exceptions;

class ZonificacionNoEncontrado extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Zonificación no encontrada');
    }
}
