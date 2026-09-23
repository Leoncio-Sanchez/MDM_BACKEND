<?php

declare(strict_types=1);

namespace App\Modules\Pide\Application\DTOs;

final readonly class ConsultaReniecDTO
{
    public function __construct(
        public string $dni,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dni: (string) ($data['dni'] ?? ''),
        );
    }
}
