<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Domain\ValueObjects;

enum TipoTramite: string
{
    case Expediente = 'expediente';
    case Licencia = 'licencia';
    case Itse = 'itse';
}
