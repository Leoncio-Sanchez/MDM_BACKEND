<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\DTOs;

final readonly class CrearLicenciaDTO {
    public function __construct(
        public string $numeroLicencia,
        public string $nombreComercial,
        public int $tipoLicenciaId,
        public ?int $titularId,
        public ?int $expedienteId,
        public ?int $nivelRiesgoId,
        public ?int $zonificacionId
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            numeroLicencia: (string) ($data['numero_licencia'] ?? ''),
            nombreComercial: (string) ($data['nombre_comercial'] ?? ''),
            tipoLicenciaId: (int) ($data['tipo_licencia_id'] ?? 0),
            titularId: isset($data['titular_id']) ? (int) $data['titular_id'] : null,
            expedienteId: isset($data['expediente_id']) ? (int) $data['expediente_id'] : null,
            nivelRiesgoId: isset($data['nivel_riesgo_id']) ? (int) $data['nivel_riesgo_id'] : null,
            zonificacionId: isset($data['zonificacion_id']) ? (int) $data['zonificacion_id'] : null
        );
    }
}
