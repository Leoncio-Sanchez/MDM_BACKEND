<?php

declare(strict_types=1);

namespace App\Modules\Giros\Domain\Exceptions;

class GiroNoEncontrado extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Giro no encontrado');
    }
}
