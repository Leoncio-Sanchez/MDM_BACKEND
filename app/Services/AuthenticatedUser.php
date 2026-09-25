<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final readonly class AuthenticatedUser
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        public string $role,
        public ?string $ruc = null,
        public string $passwordHash = '',
    ) {}

    public static function fromModel(User $user): self
    {
        $role = ($user->is_superuser || $user->is_staff) ? 'ADMIN_MUNICIPALIDAD' : 'CIUDADANO';

        return new self(
            id: (int) $user->id,
            username: (string) $user->username,
            email: (string) ($user->email ?? ''),
            role: $role,
            ruc: $user->ruc ?? null,
            passwordHash: (string) $user->password,
        );
    }
}
