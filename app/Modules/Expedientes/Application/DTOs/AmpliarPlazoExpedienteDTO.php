<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\DTOs;

use DateTimeImmutable;

final readonly class AmpliarPlazoExpedienteDTO
{
    public function __construct(
        public int $expedienteId,
        public DateTimeImmutable $fechaSuspension,
        public int $diasAmpliacion,
        public string $motivoAmpliacion,
        public int $usuarioId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(int $expedienteId, array $data, int $usuarioId): self
    {
        $fechaSuspension = isset($data['fecha_suspension']) && $data['fecha_suspension'] instanceof DateTimeImmutable
            ? $data['fecha_suspension']
            : new DateTimeImmutable((string) $data['fecha_suspension'], new \DateTimeZone('America/Lima'));

        return new self(
            expedienteId: $expedienteId,
            fechaSuspension: $fechaSuspension,
            diasAmpliacion: (int) $data['dias_ampliacion'],
            motivoAmpliacion: (string) $data['motivo_ampliacion'],
            usuarioId: $usuarioId,
        );
    }
}
