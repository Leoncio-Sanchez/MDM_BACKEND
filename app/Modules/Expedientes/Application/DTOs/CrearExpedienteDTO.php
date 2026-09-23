<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\DTOs;

use DateTimeImmutable;

final readonly class CrearExpedienteDTO
{
    public function __construct(
        public ?int $numeroExpediente,
        public DateTimeImmutable $fechaRecepcion,
        public int $tipoProcedimientoTupaId,
        public int $solicitanteId,
        public int $usuarioId,
        public ?int $representanteId = null,
        public ?string $observaciones = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, int $usuarioId): self
    {
        $fechaRecepcion = isset($data['fecha_recepcion']) && $data['fecha_recepcion'] instanceof DateTimeImmutable
            ? $data['fecha_recepcion']
            : new DateTimeImmutable((string) ($data['fecha_recepcion'] ?? 'now'), new \DateTimeZone('America/Lima'));

        return new self(
            numeroExpediente: isset($data['numero_expediente']) ? (int) $data['numero_expediente'] : null,
            fechaRecepcion: $fechaRecepcion,
            tipoProcedimientoTupaId: (int) $data['tipo_procedimiento_tupa_id'],
            solicitanteId: (int) $data['solicitante_id'],
            usuarioId: $usuarioId,
            representanteId: isset($data['representante_id']) ? (int) $data['representante_id'] : null,
            observaciones: isset($data['observaciones']) ? (string) $data['observaciones'] : null,
        );
    }
}
