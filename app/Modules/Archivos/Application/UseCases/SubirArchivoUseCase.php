<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Application\UseCases;

use App\Modules\Archivos\Application\DTOs\SubirArchivoDTO;
use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Exceptions\ErrorAlGuardarArchivo;
use App\Modules\Archivos\Domain\Exceptions\RegistroPadreNoEncontrado;
use DateTimeImmutable;
use Throwable;

final readonly class SubirArchivoUseCase
{
    public function __construct(
        private ArchivoManagerInterface $manager,
    ) {}

    public function execute(
        ArchivoRepositoryInterface $repository,
        SubirArchivoDTO $dto,
    ): ArchivoMetadata {
        if (! $repository->existePadre($dto->parentId)) {
            throw RegistroPadreNoEncontrado::conId($dto->parentId);
        }

        $carpeta = "{$dto->carpetaBase}/{$dto->parentId}";

        // 1. Guardar archivo físico en disco
        $info = $this->manager->guardar($dto->archivo, $carpeta);

        // 2. Insertar registro en BD con compensación
        try {
            $metadata = new ArchivoMetadata(
                id: null,
                uuid: $info['uuid'],
                nombreOriginal: $info['nombre_original'],
                nombreArchivo: $info['nombre_archivo'],
                rutaArchivo: $info['ruta_archivo'],
                extension: $info['extension'],
                tamanioBytes: $info['tamanio_bytes'],
                fechaDigitacion: new DateTimeImmutable,
                parentId: $dto->parentId,
                usuarioId: $dto->usuarioId,
            );

            return $repository->crear($metadata);
        } catch (Throwable $e) {
            // Compensación: eliminar archivo huérfano de disco
            $this->manager->eliminar($info['ruta_archivo']);

            throw ErrorAlGuardarArchivo::porFallo($e->getMessage());
        }
    }
}
