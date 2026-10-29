<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\Exceptions;

final class ActaInvalida extends \DomainException
{
    public static function dictamenFinal(int $numeroExpediente): self
    {
        return new self("El expediente N° {$numeroExpediente} ya tiene dictamen final: no admite más actas.");
    }

    public static function fechaFutura(): self
    {
        return new self('La fecha y hora de la visita no puede ser posterior al momento actual.');
    }

    public static function fechaAnteriorALaPrimeraVisita(): self
    {
        return new self('La reinspección debe ser posterior a la primera visita.');
    }

    public static function faltaObservaciones(): self
    {
        return new self('Detalle las observaciones encontradas: hay puntos que no cumplen.');
    }

    public static function faltaPlazo(): self
    {
        return new self('Indique el plazo de subsanación (2 o 3 días hábiles).');
    }

    public static function faltaFoto(string $deQue): self
    {
        return new self("Falta la foto de {$deQue}: es obligatoria como sustento del acta.");
    }
}
