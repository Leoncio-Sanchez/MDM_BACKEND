<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\DTOs;

final readonly class InactivarItseDTO
{
    public function __construct(
        public int $itseId,
        public string $observaciones,
        public int $usuarioId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            itseId: (int) $data['itse_id'],
            observaciones: (string) $data['observaciones'],
            usuarioId: (int) $data['usuario_id'],
        );
    }
}
