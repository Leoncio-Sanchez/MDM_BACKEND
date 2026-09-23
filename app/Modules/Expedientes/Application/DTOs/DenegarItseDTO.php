<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\DTOs;

use DateTimeImmutable;

final readonly class DenegarItseDTO
{
    public function __construct(
        public int $expedienteId,
        public DateTimeImmutable $fechaRechazo,
        public string $documento,
        public string $observaciones,
        public int $usuarioId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(int $expedienteId, array $data, int $usuarioId): self
    {
        $fechaRechazo = isset($data['fecha_rechazo']) && $data['fecha_rechazo'] instanceof DateTimeImmutable
            ? $data['fecha_rechazo']
            : new DateTimeImmutable((string) $data['fecha_rechazo'], new \DateTimeZone('America/Lima'));

        return new self(
            expedienteId: $expedienteId,
            fechaRechazo: $fechaRechazo,
            documento: (string) $data['documento'],
            observaciones: (string) $data['observaciones'],
            usuarioId: $usuarioId,
        );
    }
}
