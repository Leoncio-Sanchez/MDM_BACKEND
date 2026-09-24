<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\ValueObjects;

final readonly class TokenPair
{
    public function __construct(
        public string $access,
        public string $refresh,
    ) {}

    /** @return array{access: string, refresh: string} */
    public function toArray(): array
    {
        return [
            'access' => $this->access,
            'refresh' => $this->refresh,
        ];
    }
}
