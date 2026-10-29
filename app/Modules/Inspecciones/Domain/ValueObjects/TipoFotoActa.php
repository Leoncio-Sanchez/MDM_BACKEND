<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\ValueObjects;

enum TipoFotoActa: string
{
    case Fachada = 'FACHADA';
    case Extintores = 'EXTINTORES';
    case Tableros = 'TABLEROS';
    case Otra = 'OTRA';
}
