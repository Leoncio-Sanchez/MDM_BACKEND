<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\ValueObjects;

/** Valores de autorizaciones_improcedentes.tipo_autorizacion */
enum TipoAutorizacion: string
{
    case Licencia = 'LF';
    case Itse = 'ITSE';
}
