<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Contracts;

use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;

interface ExpedienteArchivoRepositoryInterface
{
    public function crear(ArchivoMetadata $metadata): ArchivoMetadata;

    public function obtenerPorId(int $id): ?ArchivoMetadata;

    public function obtenerPorUuid(string $uuid): ?ArchivoMetadata;

    /**
     * @return ArchivoMetadata[]
     */
    public function listarPorExpediente(int $expedienteId): array;

    public function eliminar(int $id): void;
}
