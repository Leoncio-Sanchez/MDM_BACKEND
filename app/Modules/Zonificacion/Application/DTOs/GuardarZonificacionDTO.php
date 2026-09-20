<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Application\DTOs;

final readonly class GuardarZonificacionDTO
{
    public function __construct(
        public string $codigo,
        public string $nombre,
        public bool $estaActivo,
        public int $usuarioId
    ) {}
}
