<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Application\DTOs;

final readonly class GuardarNivelRiesgoDTO
{
    public function __construct(
        public string $codigo,
        public string $nombre,
        public bool $esta_activo
    ) {}
}
