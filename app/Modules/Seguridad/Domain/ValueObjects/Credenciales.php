<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\ValueObjects;

final readonly class Credenciales
{
    public function __construct(
        public string $username,
        public string $password,
    ) {}
}
