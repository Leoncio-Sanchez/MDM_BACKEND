<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Entities;

final readonly class Licencia
{
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public int $numeroLicencia,
        public string $fechaEmision,
        public string $nombreComercial,
        public int $titularId,
        public int $expedienteId
    ) {}

    public static function crear(
        int $numeroLicencia,
        string $fechaEmision,
        string $nombreComercial,
        int $titularId,
        int $expedienteId
    ): self {
        return new self(
            id: null,
            uuid: null,
            numeroLicencia: $numeroLicencia,
            fechaEmision: $fechaEmision,
            nombreComercial: $nombreComercial,
            titularId: $titularId,
            expedienteId: $expedienteId
        );
    }
}
