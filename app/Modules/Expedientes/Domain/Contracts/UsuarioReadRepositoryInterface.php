<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Contracts;

interface UsuarioReadRepositoryInterface
{
    public function buscarPorId(int $id): ?object;

    public function existe(int $id): bool;
}
