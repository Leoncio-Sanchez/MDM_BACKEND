<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Domain\Entities;

use DateTimeImmutable;

final readonly class Zonificacion
{
    public function __construct(
        public ?int $id,
        public string $codigo,
        public string $nombre,
        public bool $estaActivo,
        public DateTimeImmutable $fechaDigitacion,
        public int $usuarioId
    ) {}

    public static function crear(string $codigo, string $nombre, bool $estaActivo, int $usuarioId): self
    {
        return new self(
            id: null,
            codigo: strtoupper(trim($codigo)),
            nombre: trim($nombre),
            estaActivo: $estaActivo,
            fechaDigitacion: new DateTimeImmutable,
            usuarioId: $usuarioId
        );
    }

    public function actualizar(?string $codigo, ?string $nombre, ?bool $estaActivo, ?int $usuarioId): self
    {
        return new self(
            id: $this->id,
            codigo: $codigo !== null ? strtoupper(trim($codigo)) : $this->codigo,
            nombre: $nombre ?? $this->nombre,
            estaActivo: $estaActivo ?? $this->estaActivo,
            fechaDigitacion: $this->fechaDigitacion,
            usuarioId: $usuarioId ?? $this->usuarioId
        );
    }
}
