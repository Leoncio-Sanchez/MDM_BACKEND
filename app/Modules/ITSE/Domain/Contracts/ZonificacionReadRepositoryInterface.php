<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

interface ZonificacionReadRepositoryInterface
{
    public function existe(int $id): bool;
}
