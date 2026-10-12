<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\DTOs;

use Carbon\CarbonImmutable;

final readonly class InactivarItseDTO
{
    public function __construct(
        public int $itseId,
        public int $estadoId,
        public CarbonImmutable $fechaEstado,
        public string $documento,
        public string $observaciones,
        public int $usuarioId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            itseId: (int) $data['itse_id'],
            estadoId: (int) $data['estado_id'],
            fechaEstado: CarbonImmutable::parse((string) $data['fecha_estado']),
            documento: (string) $data['documento'],
            observaciones: (string) $data['observaciones'],
            usuarioId: (int) $data['usuario_id'],
        );
    }
}
