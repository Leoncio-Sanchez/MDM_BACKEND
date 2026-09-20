<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\DTOs;

final readonly class VerificarExpedienteDTO {
    public function __construct(
        public string $numero,
        public string $anio
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            numero: (string) ($data['numero'] ?? ''),
            anio: (string) ($data['anio'] ?? '')
        );
    }
}
