<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Application\DTOs;

use Carbon\CarbonImmutable;

final readonly class InactivarLicenciaDTO
{
    public function __construct(
        public int $licenciaId,
        public int $estadoId,
        public CarbonImmutable $fechaEstado,
        public string $documento,
        public string $observaciones,
        public ?int $usuarioId = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            licenciaId: (int) $data['licencia_id'],
            estadoId: (int) $data['estado_id'],
            fechaEstado: CarbonImmutable::parse($data['fecha_estado']),
            documento: (string) $data['documento'],
            observaciones: (string) ($data['observaciones'] ?? ''),
            usuarioId: isset($data['usuario_id']) ? (int) $data['usuario_id'] : null,
        );
    }
}
