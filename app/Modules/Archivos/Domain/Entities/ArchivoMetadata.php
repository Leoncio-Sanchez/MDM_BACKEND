<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Entities;

use DateTimeImmutable;

/**
 * Metadatos de un archivo almacenado.
 *
 * Entidad genérica que replica las 10 columnas comunes de las tablas
 * expedientes_archivos, itse_archivos y licencias_funcionamiento_archivos.
 */
final readonly class ArchivoMetadata
{
    public function __construct(
        public ?int $id,
        public string $uuid,
        public string $nombreOriginal,
        public string $nombreArchivo,
        public string $rutaArchivo,
        public string $extension,
        public int $tamanioBytes,
        public DateTimeImmutable $fechaDigitacion,
        public int $parentId,
        public int $usuarioId,
        /** null = activo; con valor = dado de baja (se conserva para auditoría) */
        public ?InactivacionArchivo $inactivacion = null,
    ) {}

    public function estaActivo(): bool
    {
        return $this->inactivacion === null;
    }
}
