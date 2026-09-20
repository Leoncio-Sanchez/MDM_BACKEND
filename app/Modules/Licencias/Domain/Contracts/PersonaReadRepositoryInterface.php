<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Contracts;

interface PersonaReadRepositoryInterface
{
    public function buscarPorId(int $id): ?object;
    public function buscarPorDocumento(string $documento): ?object;
}
