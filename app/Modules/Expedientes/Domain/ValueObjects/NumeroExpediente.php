<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\ValueObjects;

use App\Modules\Expedientes\Domain\Exceptions\ExpedienteInvalido;

final readonly class NumeroExpediente
{
    public function __construct(public int $valor)
    {
        if ($this->valor <= 0) {
            throw ExpedienteInvalido::numeroInvalido($this->valor);
        }
    }
}
