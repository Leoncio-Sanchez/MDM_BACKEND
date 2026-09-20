<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Application\UseCases;

use App\Modules\TipoDocumentoIdentidad\Domain\Contracts\TipoDocumentoIdentidadRepositoryInterface;

final readonly class EliminarTipoDocumentoIdentidadUseCase
{
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}

    public function execute(int $id): void
    {
        $this->repository->eliminar($id);
    }
}
