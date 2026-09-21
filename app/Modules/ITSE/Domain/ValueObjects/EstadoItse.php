<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\ValueObjects;

enum EstadoItse: int
{
    case INICIADO = 1;
    case EN_TRAMITE = 2;
    case OBSERVADO = 3;
    case APROBADO = 4;
    case RECHAZADO = 5;
    case VIGENTE = 6;
    case VENCIDO = 7;
    case INACTIVO = 8;
}
