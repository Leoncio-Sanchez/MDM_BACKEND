<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Application\UseCases;

use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Exceptions\ArchivoNoEncontrado;
use Throwable;

final readonly class EliminarArchivoUseCase
{
    public function __construct(
        private ArchivoManagerInterface $manager,
    ) {}

    public function execute(
        ArchivoRepositoryInterface $repository,
        int $archivoId,
    ): void {
        $metadata = $repository->obtenerPorId($archivoId)
            ?? throw ArchivoNoEncontrado::porId($archivoId);

        // 1. Eliminar registro en BD primero
        $repository->eliminar($archivoId);

        // 2. Eliminar archivo físico de disco (best-effort, sin romper si falla)
        try {
            $this->manager->eliminar($metadata->rutaArchivo);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
