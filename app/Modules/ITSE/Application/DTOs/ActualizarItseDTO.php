<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\DTOs;

use DateTimeImmutable;

final readonly class ActualizarItseDTO
{
    public function __construct(
        public int $id,
        public int $numeroItse,
        public DateTimeImmutable $fechaExpedicion,
        public ?DateTimeImmutable $fechaSolicitudRenovacion,
        public DateTimeImmutable $fechaCaducidad,
        public string $nombreComercial,
        public string $direccion,
        public string $resolucionNumero,
        public float $area,
        public int $capacidadAforo,
        public string $numeroReciboPago,
        public ?string $observaciones,
        public bool $sePuedePublicar,
        public int $tipoItseId,
        public int $nivelRiesgoId,
        public int $conductorId,
        public int $titularId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            numeroItse: (int) $data['numero_itse'],
            fechaExpedicion: new DateTimeImmutable($data['fecha_expedicion']),
            fechaSolicitudRenovacion: !empty($data['fecha_solicitud_renovacion']) ? new DateTimeImmutable($data['fecha_solicitud_renovacion']) : null,
            fechaCaducidad: new DateTimeImmutable($data['fecha_caducidad']),
            nombreComercial: (string) $data['nombre_comercial'],
            direccion: (string) $data['direccion'],
            resolucionNumero: (string) $data['resolucion_numero'],
            area: (float) $data['area'],
            capacidadAforo: (int) ($data['capacidad_aforo'] ?? 0),
            numeroReciboPago: (string) $data['numero_recibo_pago'],
            observaciones: $data['observaciones'] ?? null,
            sePuedePublicar: (bool) ($data['se_puede_publicar'] ?? false),
            tipoItseId: (int) $data['tipo_itse_id'],
            nivelRiesgoId: (int) $data['nivel_riesgo_id'],
            conductorId: (int) $data['conductor_id'],
            titularId: (int) $data['titular_id'],
        );
    }
}
