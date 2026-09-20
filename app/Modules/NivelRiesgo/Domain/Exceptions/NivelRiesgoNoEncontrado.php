<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Domain\Exceptions;

class NivelRiesgoNoEncontrado extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Nivel de Riesgo no encontrado');
    }
}
