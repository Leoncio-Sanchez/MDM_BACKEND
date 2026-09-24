<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Application\DTOs;

final readonly class VerifyTokenDTO
{
    public function __construct(public string $token) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(token: (string) ($data['token'] ?? ''));
    }
}
