<?php

declare(strict_types=1);

namespace App\Modules\Pide\Application\DTOs;

final readonly class ConsultaSunatDTO
{
    public function __construct(
        public string $ruc,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ruc: (string) ($data['ruc'] ?? ''),
        );
    }
}
