<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Entities;

use DateTimeImmutable;

final readonly class ItseEstado
{
    public function __construct(
        public ?int $id,
        public DateTimeImmutable $fechaEstado,
        public string $documento,
        public string $observaciones,
        public DateTimeImmutable $fechaDigitacion,
        public int $estadoId,
        public int $itseId,
        public int $usuarioId,
    ) {}

    public static function crear(
        DateTimeImmutable $fechaEstado,
        string $documento,
        string $observaciones,
        DateTimeImmutable $fechaDigitacion,
        int $estadoId,
        int $itseId,
        int $usuarioId,
    ): self {
        return new self(
            id: null,
            fechaEstado: $fechaEstado,
            documento: $documento,
            observaciones: $observaciones,
            fechaDigitacion: $fechaDigitacion,
            estadoId: $estadoId,
            itseId: $itseId,
            usuarioId: $usuarioId,
        );
    }
}
