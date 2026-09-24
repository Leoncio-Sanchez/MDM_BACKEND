<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\ValueObjects;

final readonly class AccessToken
{
    public function __construct(
        public string $value,
        public int $expiresAt,
    ) {}
}
