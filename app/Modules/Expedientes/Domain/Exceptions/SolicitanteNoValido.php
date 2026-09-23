<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use DomainException;

final class SolicitanteNoValido extends DomainException
{
    public static function noExiste(int $id): self
    {
        return new self("La persona solicitante con ID {$id} no existe.");
    }

    public static function representanteNoExiste(int $id): self
    {
        return new self("La persona representante con ID {$id} no existe.");
    }
}
