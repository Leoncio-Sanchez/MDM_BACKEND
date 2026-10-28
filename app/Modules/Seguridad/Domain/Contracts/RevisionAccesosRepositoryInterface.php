<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\Contracts;

use App\Modules\Seguridad\Domain\ValueObjects\PersonalSinAccesos;

interface RevisionAccesosRepositoryInterface
{
    /** @return list<PersonalSinAccesos> ordenados por usuario */
    public function personalSinAccesos(): array;
}
