<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\DTOs;

final readonly class ConsultaExpedientesDTO
{
    public function __construct(
        public ?int $numeroExpediente = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            numeroExpediente: isset($data['numero_expediente']) && $data['numero_expediente'] !== ''
                ? (int) $data['numero_expediente']
                : null,
        );
    }
}
