<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Application\DTOs;

final readonly class RefreshTokenDTO
{
    public function __construct(public string $refresh) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(refresh: (string) ($data['refresh'] ?? ''));
    }
}
