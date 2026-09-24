<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Application\UseCases;

use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Exceptions\ArchivoFisicoNoEncontrado;
use App\Modules\Archivos\Domain\Exceptions\ArchivoNoEncontrado;

final readonly class DescargarArchivoUseCase
{
    public function __construct(
        private ArchivoManagerInterface $manager,
    ) {}

    /**
     * @return array{metadata: ArchivoMetadata, contenido: mixed}
     */
    public function execute(
        ArchivoRepositoryInterface $repository,
        string $uuid,
    ): array {
        $metadata = $repository->obtenerPorUuid($uuid)
            ?? throw ArchivoNoEncontrado::porUuid($uuid);

        if (! $this->manager->existe($metadata->rutaArchivo)) {
            throw ArchivoFisicoNoEncontrado::enRuta($metadata->rutaArchivo);
        }

        return [
            'metadata' => $metadata,
            'contenido' => $this->manager->leer($metadata->rutaArchivo),
        ];
    }
}
