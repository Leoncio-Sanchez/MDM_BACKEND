<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Contracts;

use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;

interface ArchivoRepositoryInterface
{
    public function crear(ArchivoMetadata $metadata): ArchivoMetadata;

    public function obtenerPorId(int $id): ?ArchivoMetadata;

    public function obtenerPorUuid(string $uuid): ?ArchivoMetadata;

    /**
     * @return ArchivoMetadata[]
     */
    public function listarPorParent(int $parentId): array;

    public function eliminar(int $id): void;

    /** ¿Existe el expediente / licencia / ITSE al que se adjunta? */
    public function existePadre(int $parentId): bool;
}
