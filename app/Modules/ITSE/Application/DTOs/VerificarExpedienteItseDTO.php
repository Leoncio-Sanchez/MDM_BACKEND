<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\DTOs;

final readonly class VerificarExpedienteItseDTO
{
    public function __construct(
        public int $numeroExpediente,
        public int $anio,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            numeroExpediente: (int) $data['numero_expediente'],
            anio: (int) $data['anio'],
        );
    }
}
