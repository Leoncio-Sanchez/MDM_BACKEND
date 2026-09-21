<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\DTOs;

final readonly class PorRenovarItseDTO
{
    public function __construct(
        public ?string $fechaDesde,
        public ?string $fechaHasta,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fechaDesde: isset($data['fecha_desde']) ? (string) $data['fecha_desde'] : null,
            fechaHasta: isset($data['fecha_hasta']) ? (string) $data['fecha_hasta'] : null,
        );
    }
}
