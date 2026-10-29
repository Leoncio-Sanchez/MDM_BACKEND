<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Application\UseCases;

use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Entities\InactivacionArchivo;
use App\Modules\Archivos\Domain\Exceptions\ArchivoNoEncontrado;
use App\Modules\Archivos\Domain\Exceptions\ArchivoYaInactivo;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Los adjuntos no se borran (ni el registro ni el archivo en disco): se dan de baja con motivo,
 * fecha y usuario, y se siguen pudiendo consultar y descargar por si el OCI audita.
 */
final readonly class InactivarArchivoUseCase
{
    public function execute(
        ArchivoRepositoryInterface $repository,
        int $archivoId,
        string $motivo,
        int $usuarioId,
    ): ArchivoMetadata {
        return DB::transaction(function () use ($repository, $archivoId, $motivo, $usuarioId): ArchivoMetadata {
            $archivo = $repository->obtenerPorId($archivoId)
                ?? throw ArchivoNoEncontrado::porId($archivoId);

            if (! $archivo->estaActivo()) {
                throw ArchivoYaInactivo::nombre($archivo->nombreOriginal);
            }

            $repository->inactivar($archivoId, new InactivacionArchivo(
                motivo: $motivo,
                fechaInactivacion: new DateTimeImmutable,
                usuarioId: $usuarioId,
            ));

            /** @var ArchivoMetadata */
            return $repository->obtenerPorId($archivoId);
        });
    }
}
