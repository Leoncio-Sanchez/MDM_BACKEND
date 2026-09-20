<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\DTOs;

final readonly class ReporteLicenciasDTO {
    public function __construct(
        public string $fechaInicio,
        public string $fechaFin
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            fechaInicio: (string) ($data['fecha_inicio'] ?? ''),
            fechaFin: (string) ($data['fecha_fin'] ?? '')
        );
    }
}
