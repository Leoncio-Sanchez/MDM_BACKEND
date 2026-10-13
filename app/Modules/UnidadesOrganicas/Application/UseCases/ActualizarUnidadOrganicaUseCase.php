<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Application\UseCases;

use App\Modules\UnidadesOrganicas\Application\DTOs\GuardarUnidadOrganicaDTO;
use App\Modules\UnidadesOrganicas\Domain\Contracts\UnidadOrganicaRepositoryInterface;
use App\Modules\UnidadesOrganicas\Domain\Entities\UnidadOrganica;
use App\Modules\UnidadesOrganicas\Domain\Exceptions\UnidadOrganicaNoEncontrada;

final readonly class ActualizarUnidadOrganicaUseCase
{
    public function __construct(
        private UnidadOrganicaRepositoryInterface $repository
    ) {}

    public function execute(int $id, GuardarUnidadOrganicaDTO $dto): UnidadOrganica
    {
        if ($this->repository->buscarPorId($id) === null) {
            throw UnidadOrganicaNoEncontrada::porId($id);
        }

        return $this->repository->actualizar(
            UnidadOrganica::crear($dto->codigo, $dto->nombre, $dto->sigla, $dto->estaActivo, $id)
        );
    }
}
