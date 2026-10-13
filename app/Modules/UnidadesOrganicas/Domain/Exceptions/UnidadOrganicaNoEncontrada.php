<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Domain\Exceptions;

final class UnidadOrganicaNoEncontrada extends \DomainException
{
    public static function porId(int $id): self
    {
        return new self("La unidad orgánica con ID {$id} no existe.");
    }
}
