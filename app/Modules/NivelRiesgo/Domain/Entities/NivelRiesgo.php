<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Domain\Entities;

final readonly class NivelRiesgo
{
    public function __construct(
        public ?int $id,
        public string $codigo,
        public string $nombre,
        public bool $estaActivo,
    ) {}

    public static function crear(string $codigo, string $nombre, bool $estaActivo): self
    {
        return new self(
            id: null,
            codigo: strtoupper(trim($codigo)),
            nombre: trim($nombre),
            estaActivo: $estaActivo,
        );
    }

    public function actualizar(?string $codigo, ?string $nombre, ?bool $estaActivo): self
    {
        return new self(
            id: $this->id,
            codigo: $codigo !== null ? strtoupper(trim($codigo)) : $this->codigo,
            nombre: $nombre ?? $this->nombre,
            estaActivo: $estaActivo ?? $this->estaActivo,
        );
    }
}
