<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use Carbon\CarbonImmutable;

/**
 * El día de la municipalidad. La zona global sigue en UTC porque el sistema heredado guarda
 * así sus fechas: con ella, desde las 19:00 de Lima "hoy" ya sería el día siguiente.
 */
final class Calendario
{
    public const ZONA = 'America/Lima';

    /**
     * Hoy en Lima, a medianoche en la zona global: igual que una columna DATE leída con
     * CarbonImmutable::parse(), para compararlas sin que se cuelen las 5 horas de diferencia.
     */
    public static function hoy(): CarbonImmutable
    {
        return CarbonImmutable::parse(CarbonImmutable::now(self::ZONA)->toDateString());
    }
}
