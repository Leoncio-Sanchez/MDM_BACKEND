<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Application\DTOs;

/** Datos para crear o reemplazar (PUT) una unidad orgánica. */
final readonly class GuardarUnidadOrganicaDTO
{
    public function __construct(
        public string $codigo,
        public string $nombre,
        public ?string $sigla,
        public bool $estaActivo,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            codigo: (string) $data['codigo'],
            nombre: (string) $data['nombre'],
            sigla: isset($data['sigla']) ? (string) $data['sigla'] : null,
            estaActivo: (bool) ($data['esta_activo'] ?? true),
        );
    }
}
