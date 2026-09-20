<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Entities;

use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;
use Carbon\CarbonImmutable;

final readonly class Licencia
{
    public function __construct(
        public ?int $id,
        public string $uuid,
        public NumeroLicencia $numeroLicencia,
        public CarbonImmutable $fechaEmision,
        public NombreComercial $nombreComercial,
        public ?int $titularId,
        public ?int $expedienteId,
        public int $tipoLicenciaId,
        public ?int $nivelRiesgoId,
        public ?int $zonificacionId,
        public EstadoLicencia $estado,
    ) {}

    public static function crear(
        string $uuid,
        string $numeroLicencia,
        string $nombreComercial,
        int $tipoLicenciaId,
        ?int $titularId = null,
        ?int $expedienteId = null,
        ?int $nivelRiesgoId = null,
        ?int $zonificacionId = null,
    ): self {
        return new self(
            id: null,
            uuid: $uuid,
            numeroLicencia: NumeroLicencia::fromString($numeroLicencia),
            fechaEmision: CarbonImmutable::now(),
            nombreComercial: NombreComercial::fromString($nombreComercial),
            titularId: $titularId,
            expedienteId: $expedienteId,
            tipoLicenciaId: $tipoLicenciaId,
            nivelRiesgoId: $nivelRiesgoId,
            zonificacionId: $zonificacionId,
            estado: EstadoLicencia::registrada(),
        );
    }

    public function inactivar(): self
    {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            numeroLicencia: $this->numeroLicencia,
            fechaEmision: $this->fechaEmision,
            nombreComercial: $this->nombreComercial,
            titularId: $this->titularId,
            expedienteId: $this->expedienteId,
            tipoLicenciaId: $this->tipoLicenciaId,
            nivelRiesgoId: $this->nivelRiesgoId,
            zonificacionId: $this->zonificacionId,
            estado: EstadoLicencia::inactiva(),
        );
    }
}
