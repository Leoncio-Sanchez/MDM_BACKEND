<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Domain\Exceptions;

class TipoDocumentoIdentidadNoEncontrado extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Tipo de documento de identidad no encontrado');
    }
}
