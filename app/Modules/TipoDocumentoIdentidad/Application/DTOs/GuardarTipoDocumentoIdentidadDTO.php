<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Application\DTOs;

final readonly class GuardarTipoDocumentoIdentidadDTO
{
    public function __construct(
        public string $codigo,
        public string $nombre,
        public bool $estaActivo,
        public bool $esParaNatural,
        public bool $esParaJuridica
    ) {}
}
