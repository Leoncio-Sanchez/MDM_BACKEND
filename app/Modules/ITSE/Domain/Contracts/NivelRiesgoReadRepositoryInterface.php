<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

interface NivelRiesgoReadRepositoryInterface
{
    public function existe(int $id): bool;
    public function buscarPorId(int $id): ?object;
}
