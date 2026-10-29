<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\Exceptions;

final class AsignacionNoEncontrada extends \DomainException
{
    public static function id(int $id): self
    {
        return new self("No existe una asignación vigente con id {$id}.");
    }
}
