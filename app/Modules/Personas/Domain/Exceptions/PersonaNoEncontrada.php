<?php

declare(strict_types=1);

namespace App\Modules\Personas\Domain\Exceptions;

class PersonaNoEncontrada extends \DomainException
{
    public static function porId(int $id): self
    {
        return new self("Persona con ID {$id} no encontrada.");
    }
}
