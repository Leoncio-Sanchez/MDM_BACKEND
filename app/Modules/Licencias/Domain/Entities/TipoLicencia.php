<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Entities;

final readonly class TipoLicencia
{
    public function __construct(
        public int $id,
        public string $codigo,
        public string $nombre,
        public bool $estaActivo,
    ) {}
}
