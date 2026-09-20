<?php

declare(strict_types=1);

namespace App\Modules\TipoLicencia\Domain\Exceptions;

use Exception;

final class TipoLicenciaNoEncontrado extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct("Tipo de licencia con id $id no encontrado.");
    }
}
