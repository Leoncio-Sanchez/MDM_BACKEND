<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\ValueObjects;

use DateTimeImmutable;

final readonly class PlazoExpediente
{
    public function __construct(
        public int $diasAtencion,
        public int $diasAlerta,
    ) {}

    public function calcularVencimiento(DateTimeImmutable $fechaRecepcion): DateTimeImmutable
    {
        return $fechaRecepcion->modify("+{$this->diasAtencion} days");
    }

    public function calcularAlerta(DateTimeImmutable $fechaVencimiento): DateTimeImmutable
    {
        return $fechaVencimiento->modify("-{$this->diasAlerta} days");
    }
}
