<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\ValueObjects;

enum ResultadoActa: string
{
    case Aprobado = 'APROBADO';
    /** 1.ª visita con algo por corregir: plazo de subsanación y reinspección */
    case Observado = 'OBSERVADO';
    /** La reinspección encontró que no se corrigió: corresponde denegar la ITSE */
    case Desaprobado = 'DESAPROBADO';

    /** Aprobado o Desaprobado: no hay más visitas */
    public function esFinal(): bool
    {
        return $this !== self::Observado;
    }
}
