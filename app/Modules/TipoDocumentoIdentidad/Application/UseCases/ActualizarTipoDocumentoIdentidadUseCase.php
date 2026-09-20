<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Application\UseCases;

use App\Modules\TipoDocumentoIdentidad\Application\DTOs\GuardarTipoDocumentoIdentidadDTO;
use App\Modules\TipoDocumentoIdentidad\Domain\Contracts\TipoDocumentoIdentidadRepositoryInterface;
use App\Modules\TipoDocumentoIdentidad\Domain\Entities\TipoDocumentoIdentidad;
use App\Modules\TipoDocumentoIdentidad\Domain\Exceptions\TipoDocumentoIdentidadNoEncontrado;

final readonly class ActualizarTipoDocumentoIdentidadUseCase
{
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}

    public function execute(int $id, GuardarTipoDocumentoIdentidadDTO $dto): TipoDocumentoIdentidad
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new TipoDocumentoIdentidadNoEncontrado;
        }
        $updated = $entity->actualizar($dto->codigo, $dto->nombre, $dto->estaActivo, $dto->esParaNatural, $dto->esParaJuridica);

        return $this->repository->actualizar($updated);
    }
}
