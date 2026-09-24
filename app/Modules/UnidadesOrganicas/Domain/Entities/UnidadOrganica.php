<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Domain\Entities;

final class UnidadOrganica
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $codigo,
        public readonly string $nombre,
        public readonly string $sigla,
        public readonly bool $estaActivo,
    ) {}
}
