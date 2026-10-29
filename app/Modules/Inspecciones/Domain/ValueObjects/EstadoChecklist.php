<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\ValueObjects;

enum EstadoChecklist: string
{
    case Cumple = 'CUMPLE';
    case NoCumple = 'NO_CUMPLE';
    case NoAplica = 'NO_APLICA';
}
