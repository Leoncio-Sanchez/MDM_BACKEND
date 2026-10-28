<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\ValueObjects;

use DateTimeImmutable;

/** Registro de un informe formal: su correlativo y el periodo que cubre. */
final readonly class InformeEmitido
{
    public function __construct(
        public string $tipo,
        public int $anio,
        public int $numero,
        public string $periodoDesde,
        public string $periodoHasta,
        public DateTimeImmutable $fechaEmision,
        // true si el periodo ya tenía informe: se reimprime con el mismo número
        public bool $esReimpresion,
    ) {}

    /** Ej.: 012-2026-MDM/GDES */
    public function codigo(string $siglas): string
    {
        return sprintf('%03d-%d-%s', $this->numero, $this->anio, $siglas);
    }
}
