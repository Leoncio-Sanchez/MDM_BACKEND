<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Contracts;

use DateTimeImmutable;

interface FeriadoReadRepositoryInterface
{
    public function esFeriado(DateTimeImmutable $fecha): bool;
}
