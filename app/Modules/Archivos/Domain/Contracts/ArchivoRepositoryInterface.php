<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Contracts;

use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Entities\InactivacionArchivo;

interface ArchivoRepositoryInterface
{
    public function crear(ArchivoMetadata $metadata): ArchivoMetadata;

    public function obtenerPorId(int $id): ?ArchivoMetadata;

    public function obtenerPorUuid(string $uuid): ?ArchivoMetadata;

    /**
     * @return ArchivoMetadata[]
     */
    public function listarPorParent(int $parentId): array;

    /** Da de baja el archivo sin borrarlo (ni el registro ni el archivo en disco) */
    public function inactivar(int $id, InactivacionArchivo $inactivacion): void;

    /** ¿Existe el expediente / licencia / ITSE al que se adjunta? */
    public function existePadre(int $parentId): bool;
}
