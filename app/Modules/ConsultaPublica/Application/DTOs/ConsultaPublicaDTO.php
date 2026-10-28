<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Application\DTOs;

use App\Modules\ConsultaPublica\Domain\ValueObjects\TipoTramite;

final readonly class ConsultaPublicaDTO
{
    public function __construct(
        public TipoTramite $tipo,
        public int $numero,
        public string $documento,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tipo: TipoTramite::from((string) $data['tipo']),
            numero: (int) $data['numero'],
            documento: strtoupper(trim((string) $data['documento'])),
        );
    }
}
