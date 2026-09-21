<?php

declare(strict_types=1);

namespace App\Modules\Personas\Domain\ValueObjects;

enum Sexo: string
{
    case Masculino = 'M';
    case Femenino = 'F';
    case PrefieroNoDecir = 'X';
}
