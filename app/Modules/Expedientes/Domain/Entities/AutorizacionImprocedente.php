<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Entities;

use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;
use DateTimeImmutable;

final readonly class AutorizacionImprocedente
{
    public function __construct(
        public ?int $id,
        public int $expedienteId,
        public TipoAutorizacion $tipoAutorizacion,
        public DateTimeImmutable $fechaRechazo,
        public ?string $documento,
        public ?string $observaciones,
        public int $usuarioId,
        public DateTimeImmutable $fechaDigitacion,
    ) {}

    public static function crear(
        int $expedienteId,
        TipoAutorizacion $tipo,
        ?string $documento,
        ?string $observaciones,
        int $usuarioId,
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            id: null,
            expedienteId: $expedienteId,
            tipoAutorizacion: $tipo,
            fechaRechazo: $now,
            documento: $documento,
            observaciones: $observaciones,
            usuarioId: $usuarioId,
            fechaDigitacion: $now,
        );
    }
}
