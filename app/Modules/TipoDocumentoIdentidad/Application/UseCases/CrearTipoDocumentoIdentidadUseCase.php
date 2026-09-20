<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Application\UseCases;

use App\Modules\TipoDocumentoIdentidad\Application\DTOs\GuardarTipoDocumentoIdentidadDTO;
use App\Modules\TipoDocumentoIdentidad\Domain\Contracts\TipoDocumentoIdentidadRepositoryInterface;
use App\Modules\TipoDocumentoIdentidad\Domain\Entities\TipoDocumentoIdentidad;

final readonly class CrearTipoDocumentoIdentidadUseCase
{
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}

    public function execute(GuardarTipoDocumentoIdentidadDTO $dto): TipoDocumentoIdentidad
    {
        $entity = TipoDocumentoIdentidad::crear($dto->codigo, $dto->nombre, $dto->estaActivo, $dto->esParaNatural, $dto->esParaJuridica);

        return $this->repository->guardar($entity);
    }
}
