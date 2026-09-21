<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

interface AutorizacionImprocedenteReadRepositoryInterface
{
    public function existe(int $expedienteId, string $tipoAutorizacion): bool;
}
