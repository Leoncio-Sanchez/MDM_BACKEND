<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Application\DTOs;

use DateTimeImmutable;

final readonly class ActualizarExpedienteDTO
{
    public function __construct(
        public int $expedienteId,
        public int $numeroExpediente,
        public DateTimeImmutable $fechaRecepcion,
        public int $tipoProcedimientoTupaId,
        public int $solicitanteId,
        public ?int $representanteId = null,
        public ?string $observaciones = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(int $expedienteId, array $data): self
    {
        $fechaRecepcion = isset($data['fecha_recepcion']) && $data['fecha_recepcion'] instanceof DateTimeImmutable
            ? $data['fecha_recepcion']
            : new DateTimeImmutable((string) ($data['fecha_recepcion'] ?? 'now'), new \DateTimeZone('America/Lima'));

        return new self(
            expedienteId: $expedienteId,
            numeroExpediente: (int) $data['numero_expediente'],
            fechaRecepcion: $fechaRecepcion,
            tipoProcedimientoTupaId: (int) $data['tipo_procedimiento_tupa_id'],
            solicitanteId: (int) $data['solicitante_id'],
            representanteId: isset($data['representante_id']) ? (int) $data['representante_id'] : null,
            observaciones: isset($data['observaciones']) ? (string) $data['observaciones'] : null,
        );
    }
}
