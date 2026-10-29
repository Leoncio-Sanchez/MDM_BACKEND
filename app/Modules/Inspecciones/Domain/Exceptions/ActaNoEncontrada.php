<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\Exceptions;

final class ActaNoEncontrada extends \DomainException
{
    public static function id(int $id): self
    {
        return new self("No existe un acta con id {$id} a su alcance.");
    }
}
