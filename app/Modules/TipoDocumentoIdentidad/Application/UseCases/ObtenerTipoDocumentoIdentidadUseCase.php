<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Application\UseCases;

use App\Modules\TipoDocumentoIdentidad\Domain\Contracts\TipoDocumentoIdentidadRepositoryInterface;
use App\Modules\TipoDocumentoIdentidad\Domain\Entities\TipoDocumentoIdentidad;
use App\Modules\TipoDocumentoIdentidad\Domain\Exceptions\TipoDocumentoIdentidadNoEncontrado;

final readonly class ObtenerTipoDocumentoIdentidadUseCase
{
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}

    public function execute(int $id): TipoDocumentoIdentidad
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new TipoDocumentoIdentidadNoEncontrado;
        }

        return $entity;
    }
}
