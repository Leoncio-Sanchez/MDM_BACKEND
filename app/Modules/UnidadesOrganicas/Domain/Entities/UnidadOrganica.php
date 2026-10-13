<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Domain\Entities;

final class UnidadOrganica
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $codigo,
        public readonly string $nombre,
        /** Cadena vacía cuando la unidad no tiene sigla (compatible con columna NOT NULL o nullable). */
        public readonly string $sigla,
        public readonly bool $estaActivo,
    ) {}

    /** Normaliza los datos de entrada: código y sigla en mayúsculas, sin espacios sobrantes. */
    public static function crear(string $codigo, string $nombre, ?string $sigla, bool $estaActivo = true, ?int $id = null): self
    {
        return new self(
            id: $id,
            codigo: mb_strtoupper(trim($codigo)),
            nombre: trim($nombre),
            sigla: mb_strtoupper(trim($sigla ?? '')),
            estaActivo: $estaActivo,
        );
    }
}
