<?php

declare(strict_types=1);

namespace App\Modules\Giros\Application\DTOs;

final readonly class CrearGiroDTO
{
    public function __construct(
        public ?int $ciiuId,
        public string $nombre,
        public bool $estaActivo,
        public int $usuarioId
    ) {}
}
