<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\Contracts;

use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;

interface UserRepositoryInterface
{
    public function buscarPorUsername(string $username): ?AuthenticatedUser;

    public function buscarPorId(int $id): ?AuthenticatedUser;
}
