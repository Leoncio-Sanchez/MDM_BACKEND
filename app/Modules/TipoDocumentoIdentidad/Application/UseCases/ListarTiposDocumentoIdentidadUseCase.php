<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Application\UseCases;

use App\Modules\TipoDocumentoIdentidad\Domain\Contracts\TipoDocumentoIdentidadRepositoryInterface;
use App\Modules\TipoDocumentoIdentidad\Domain\Entities\TipoDocumentoIdentidad;

final readonly class ListarTiposDocumentoIdentidadUseCase
{
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}

    /** @return TipoDocumentoIdentidad[] */
    public function execute(?string $tipoPersona = null): array
    {
        return $this->repository->obtenerTodos($tipoPersona);
    }
}
