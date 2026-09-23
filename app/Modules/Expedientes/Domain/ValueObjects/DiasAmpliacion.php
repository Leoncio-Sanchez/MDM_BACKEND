<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class DiasAmpliacion
{
    public function __construct(public int $valor)
    {
        if ($this->valor < 1) {
            throw new InvalidArgumentException("Días de ampliación debe ser >= 1, recibido: {$this->valor}");
        }
    }
}
