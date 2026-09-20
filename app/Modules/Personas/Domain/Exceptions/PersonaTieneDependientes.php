<?php
declare(strict_types=1);

namespace App\Modules\Personas\Domain\Exceptions;

class PersonaTieneDependientes extends \DomainException
{
    public static function porId(int $id): self
    {
        return new self("La persona con ID {$id} tiene dependientes y no puede ser eliminada.");
    }
}
