<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Contracts;

interface ZonificacionReadRepositoryInterface
{
    public function buscarPorId(int $id): ?object;
}
