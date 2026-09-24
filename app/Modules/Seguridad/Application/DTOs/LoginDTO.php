<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Application\DTOs;

final readonly class LoginDTO
{
    public function __construct(
        public string $username,
        public string $password,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            username: (string) ($data['username'] ?? ''),
            password: (string) ($data['password'] ?? ''),
        );
    }
}
