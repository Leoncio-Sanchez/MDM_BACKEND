<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Services;

use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use DateTimeImmutable;

final readonly class CalculadoraDiasHabilesService
{
    public function __construct(
        private FeriadoReadRepositoryInterface $feriados,
    ) {}

    public function calcularFechaVencimiento(DateTimeImmutable $fechaInicio, int $plazoDias): DateTimeImmutable
    {
        if ($plazoDias <= 0) {
            return $fechaInicio;
        }

        $fecha = $fechaInicio;
        $contador = 0;

        while ($contador < $plazoDias) {
            $fecha = $fecha->modify('+1 day');
            if ($this->esDiaHabil($fecha)) {
                $contador++;
            }
        }

        return $fecha;
    }

    public function calcularFechaAlerta(DateTimeImmutable $fechaVencimiento, int $diasAlerta): DateTimeImmutable
    {
        if ($diasAlerta <= 0) {
            return $fechaVencimiento;
        }

        $fecha = $fechaVencimiento;
        $contador = 0;

        while ($contador < $diasAlerta) {
            $fecha = $fecha->modify('-1 day');
            if ($this->esDiaHabil($fecha)) {
                $contador++;
            }
        }

        return $fecha;
    }

    public function esDiaHabil(DateTimeImmutable $fecha): bool
    {
        // Sábado = 6, Domingo = 7 (N format)
        $diaSemana = (int) $fecha->format('N');
        if ($diaSemana >= 6) {
            return false;
        }

        return ! $this->feriados->esFeriado($fecha);
    }

    public function diferenciaDiasHabiles(DateTimeImmutable $desde, DateTimeImmutable $hasta): int
    {
        // Normalizar a fechas (sin hora)
        $desdeNorm = $desde->setTime(0, 0, 0);
        $hastaNorm = $hasta->setTime(0, 0, 0);

        if ($desdeNorm == $hastaNorm) {
            return 0;
        }

        $avanzando = $hastaNorm > $desdeNorm;
        $menor = $avanzando ? $desdeNorm : $hastaNorm;
        $mayor = $avanzando ? $hastaNorm : $desdeNorm;

        $conteo = 0;
        $cursor = $menor;
        while ($cursor < $mayor) {
            $cursor = $cursor->modify('+1 day');
            if ($this->esDiaHabil($cursor)) {
                $conteo++;
            }
        }

        return $avanzando ? $conteo : -$conteo;
    }
}
