<?php

declare(strict_types=1);

namespace App\Modules\Personas\Domain\ValueObjects;

enum TipoPersona: string
{
    case Natural = 'N';
    case Juridica = 'J';

    public function label(): string
    {
        return match ($this) {
            self::Natural => 'Natural',
            self::Juridica => 'Jurídica',
        };
    }
}
