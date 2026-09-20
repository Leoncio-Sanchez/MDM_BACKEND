<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Application\DTOs;

use Carbon\CarbonImmutable;

final readonly class RegistrarNotificacionDTO
{
    public function __construct(
        public int $licenciaId,
        public CarbonImmutable $fechaNotificacion,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            licenciaId: (int) $data['licencia_id'],
            fechaNotificacion: CarbonImmutable::parse($data['fecha_notificacion']),
        );
    }
}
