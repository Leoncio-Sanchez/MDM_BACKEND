<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\ValueObjects;

use App\Shared\Domain\Calendario;

/** Rango de fechas de un reporte (Y-m-d, ambos inclusive). */
final readonly class Periodo
{
    public function __construct(
        public string $desde,
        public string $hasta,
    ) {}

    /** Sin fechas: el mes en curso */
    public static function oMesActual(?string $desde, ?string $hasta): self
    {
        $hoy = Calendario::hoy();

        return new self(
            $desde ?? $hoy->startOfMonth()->toDateString(),
            $hasta ?? $hoy->endOfMonth()->toDateString(),
        );
    }
}
