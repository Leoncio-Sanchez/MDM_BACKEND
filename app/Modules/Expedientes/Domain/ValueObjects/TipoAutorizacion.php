<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\ValueObjects;

enum TipoAutorizacion: string
{
    case LF = 'LF';
    case ITSE = 'ITSE';
}
