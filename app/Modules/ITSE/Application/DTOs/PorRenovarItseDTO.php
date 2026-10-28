<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\DTOs;

final readonly class PorRenovarItseDTO
{
    public const DIAS_POR_DEFECTO = 30;

    public function __construct(
        public ?string $fechaDesde,
        public ?string $fechaHasta,
        public int $dias = self::DIAS_POR_DEFECTO,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fechaDesde: isset($data['fecha_desde']) ? (string) $data['fecha_desde'] : null,
            fechaHasta: isset($data['fecha_hasta']) ? (string) $data['fecha_hasta'] : null,
            dias: isset($data['dias']) ? (int) $data['dias'] : self::DIAS_POR_DEFECTO,
        );
    }
}
