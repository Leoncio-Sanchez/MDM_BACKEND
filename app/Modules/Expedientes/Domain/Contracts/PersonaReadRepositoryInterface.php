<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Contracts;

interface PersonaReadRepositoryInterface
{
    /**
     * @return object|null Objeto con id, tipo_persona, etc.
     */
    public function buscarPorId(int $id): ?object;

    public function existe(int $id): bool;
}
