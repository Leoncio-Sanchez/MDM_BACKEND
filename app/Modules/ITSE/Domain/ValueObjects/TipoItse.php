<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\ValueObjects;

enum TipoItse: int
{
    case Estandar = 1;
    case Renovacion = 2;
}
