<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Domain\Entities;

final readonly class TipoDocumentoIdentidad
{
    public function __construct(
        public ?int $id,
        public string $codigo,
        public string $nombre,
        public bool $estaActivo,
        public bool $esParaNatural,
        public bool $esParaJuridica
    ) {}

    public static function crear(string $codigo, string $nombre, bool $estaActivo, bool $esParaNatural, bool $esParaJuridica): self
    {
        return new self(
            id: null,
            codigo: strtoupper(trim($codigo)),
            nombre: trim($nombre),
            estaActivo: $estaActivo,
            esParaNatural: $esParaNatural,
            esParaJuridica: $esParaJuridica
        );
    }

    public function actualizar(?string $codigo, ?string $nombre, ?bool $estaActivo, ?bool $esParaNatural, ?bool $esParaJuridica): self
    {
        return new self(
            id: $this->id,
            codigo: $codigo !== null ? strtoupper(trim($codigo)) : $this->codigo,
            nombre: $nombre ?? $this->nombre,
            estaActivo: $estaActivo ?? $this->estaActivo,
            esParaNatural: $esParaNatural ?? $this->esParaNatural,
            esParaJuridica: $esParaJuridica ?? $this->esParaJuridica
        );
    }
}
