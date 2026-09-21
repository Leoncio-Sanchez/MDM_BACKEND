<?php

declare(strict_types=1);

namespace App\Modules\Personas\Application\DTOs;

final readonly class ConsultarReniecDTO
{
    public function __construct(
        public string $dni,
    ) {}
}
