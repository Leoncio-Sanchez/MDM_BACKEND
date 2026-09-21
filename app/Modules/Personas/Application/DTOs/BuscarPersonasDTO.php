<?php

declare(strict_types=1);

namespace App\Modules\Personas\Application\DTOs;

final readonly class BuscarPersonasDTO
{
    public function __construct(
        public string $filtro,
        public string $valor,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['filtro'],
            $data['valor'],
        );
    }
}
