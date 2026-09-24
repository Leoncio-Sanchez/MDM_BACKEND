<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\ValueObjects;

final readonly class AuthenticatedUser
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        public string $role,           // 'ADMIN_MUNICIPALIDAD' | 'CIUDADANO'
        public ?string $ruc,
        public string $passwordHash,   // hash de auth_user.password (para validar)
    ) {}
}
