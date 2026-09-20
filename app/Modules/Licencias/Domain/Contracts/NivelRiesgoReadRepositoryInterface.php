<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Contracts;

interface NivelRiesgoReadRepositoryInterface
{
    public function buscarPorId(int $id): ?object;
}
