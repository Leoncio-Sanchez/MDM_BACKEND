<?php

declare(strict_types=1);

namespace App\Modules\Estados\Domain\Entities;

use App\Modules\Estados\Domain\Exceptions\EstadoNombreInvalido;

final readonly class Estado
{
    public function __construct(
        public int $id,
        public string $nombre,
        public bool $esParaLf,
        public bool $esParaItse,
        public bool $estaActivo,
    ) {}

    public static function crear(
        string $nombre,
        bool $esParaLf,
        bool $esParaItse,
    ): self {
        $nombreLimpio = trim($nombre);

        if ($nombreLimpio === '') {
            throw new EstadoNombreInvalido('El nombre no puede estar vacío');
        }

        if (mb_strlen($nombreLimpio) > 100) {
            throw new EstadoNombreInvalido('El nombre no puede exceder 100 caracteres');
        }

        return new self(
            id: 0,
            nombre: $nombreLimpio,
            esParaLf: $esParaLf,
            esParaItse: $esParaItse,
            estaActivo: false,
        );
    }

    public function actualizar(
        ?string $nombre,
        ?bool $esParaLf,
        ?bool $esParaItse,
        ?bool $estaActivo,
    ): self {
        return new self(
            id: $this->id,
            nombre: $nombre !== null ? trim($nombre) : $this->nombre,
            esParaLf: $esParaLf ?? $this->esParaLf,
            esParaItse: $esParaItse ?? $this->esParaItse,
            estaActivo: $estaActivo ?? $this->estaActivo,
        );
    }
}
