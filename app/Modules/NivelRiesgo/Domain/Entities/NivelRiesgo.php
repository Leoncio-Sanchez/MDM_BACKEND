<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Domain\Entities;

class NivelRiesgo
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $codigo,
        public readonly string $nombre,
        public readonly bool $estaActivo
    ) {}
}
