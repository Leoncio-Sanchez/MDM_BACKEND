<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\DTOs;

use DateTimeImmutable;

final readonly class NotificarItseDTO
{
    public function __construct(
        public int $itseId,
        public DateTimeImmutable $fechaNotificacion,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            itseId: (int) $data['itse_id'],
            fechaNotificacion: new DateTimeImmutable($data['fecha_notificacion']),
        );
    }
}
