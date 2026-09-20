<?php

declare(strict_types=1);

namespace App\Modules\Giros\Domain\Entities;

final readonly class Giro
{
    public function __construct(
        public ?int $id,
        public ?int $ciiuId,
        public string $nombre,
        public bool $estaActivo,
        public ?\DateTimeImmutable $fechaDigitacion,
        public int $usuarioId
    ) {}

    public static function crear(?int $ciiuId, string $nombre, bool $estaActivo, int $usuarioId): self
    {
        return new self(
            id: null,
            ciiuId: $ciiuId,
            nombre: trim($nombre),
            estaActivo: $estaActivo,
            fechaDigitacion: new \DateTimeImmutable,
            usuarioId: $usuarioId
        );
    }

    public function actualizar(?int $ciiuId, ?string $nombre, ?bool $estaActivo, int $usuarioId): self
    {
        return new self(
            id: $this->id,
            ciiuId: $ciiuId !== null ? $ciiuId : $this->ciiuId,
            nombre: $nombre ?? $this->nombre,
            estaActivo: $estaActivo ?? $this->estaActivo,
            fechaDigitacion: $this->fechaDigitacion,
            usuarioId: $usuarioId
        );
    }
}
