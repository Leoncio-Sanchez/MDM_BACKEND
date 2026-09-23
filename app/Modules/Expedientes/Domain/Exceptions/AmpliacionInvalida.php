<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use DomainException;

final class AmpliacionInvalida extends DomainException
{
    public static function diasInvalidos(int $dias): self
    {
        return new self("Los días de ampliación ({$dias}) deben ser mayores a cero.");
    }

    public static function yaTieneAmpliacion(int $id): self
    {
        return new self("El expediente con ID {$id} ya tiene una ampliación de plazo registrada.");
    }
}
