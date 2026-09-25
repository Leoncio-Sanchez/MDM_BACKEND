<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

final class CalculadoraDiasHabiles
{
    public function esDiaHabil(DateTimeInterface $fecha): bool
    {
        $diaSemana = (int) $fecha->format('N');
        if ($diaSemana >= 6) {
            return false;
        }

        $dia = (int) $fecha->format('j');
        $mes = (int) $fecha->format('n');

        $recurrente = DB::table('feriados_recurrentes')
            ->where('dia', $dia)
            ->where('mes', $mes)
            ->exists();

        if ($recurrente) {
            return false;
        }

        $fechaStr = $fecha->format('Y-m-d');
        $anual = DB::table('feriados_anuales')
            ->whereDate('feriado', $fechaStr)
            ->exists();

        return ! $anual;
    }

    public function calcularFechaVencimiento(DateTimeInterface $fechaInicio, int $plazoDias): DateTimeImmutable
    {
        $fecha = DateTimeImmutable::createFromInterface($fechaInicio);
        if ($plazoDias <= 0) {
            return $fecha;
        }

        $contador = 0;
        while ($contador < $plazoDias) {
            $fecha = $fecha->modify('+1 day');
            if ($this->esDiaHabil($fecha)) {
                $contador++;
            }
        }

        return $fecha;
    }

    public function calcularFechaAlerta(DateTimeInterface $fechaVencimiento, int $diasAlerta): DateTimeImmutable
    {
        $fecha = DateTimeImmutable::createFromInterface($fechaVencimiento);
        if ($diasAlerta <= 0) {
            return $fecha;
        }

        $contador = 0;
        while ($contador < $diasAlerta) {
            $fecha = $fecha->modify('-1 day');
            if ($this->esDiaHabil($fecha)) {
                $contador++;
            }
        }

        return $fecha;
    }

    public function avanzarDiasHabiles(DateTimeInterface $fecha, int $dias): DateTimeImmutable
    {
        return $this->calcularFechaVencimiento($fecha, $dias);
    }

    public function retrocederDiasHabiles(DateTimeInterface $fecha, int $dias): DateTimeImmutable
    {
        return $this->calcularFechaAlerta($fecha, $dias);
    }

    public function diferenciaDiasHabiles(DateTimeInterface $desde, DateTimeInterface $hasta): int
    {
        $desdeNorm = DateTimeImmutable::createFromInterface($desde)->setTime(0, 0, 0);
        $hastaNorm = DateTimeImmutable::createFromInterface($hasta)->setTime(0, 0, 0);

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
