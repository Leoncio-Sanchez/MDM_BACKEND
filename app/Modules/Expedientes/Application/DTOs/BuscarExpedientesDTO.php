<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\DTOs;

final readonly class BuscarExpedientesDTO
{
    public function __construct(
        public string $filtro,
        public string $valor,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            filtro: (string) ($data['filtro'] ?? ''),
            valor: (string) ($data['valor'] ?? ''),
        );
    }
}
