<?php

declare(strict_types=1);

namespace App\Modules\TipoLicencia\Domain\Entities;

final class TipoLicencia
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $codigo,
        public readonly string $nombre,
        public readonly bool $estaActivo
    ) {}
}
