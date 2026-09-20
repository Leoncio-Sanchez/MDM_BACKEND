<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\DTOs;

final readonly class RegistrarNotificacionDTO {
    public function __construct(
        public int $licenciaId,
        public string $fecha
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            licenciaId: (int) ($data['licencia_id'] ?? 0),
            fecha: (string) ($data['fecha'] ?? '')
        );
    }
}
