<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Application\UseCases;

use App\Modules\UnidadesOrganicas\Application\DTOs\GuardarUnidadOrganicaDTO;
use App\Modules\UnidadesOrganicas\Domain\Contracts\UnidadOrganicaRepositoryInterface;
use App\Modules\UnidadesOrganicas\Domain\Entities\UnidadOrganica;

final readonly class CrearUnidadOrganicaUseCase
{
    public function __construct(
        private UnidadOrganicaRepositoryInterface $repository
    ) {}

    public function execute(GuardarUnidadOrganicaDTO $dto): UnidadOrganica
    {
        return $this->repository->guardar(
            UnidadOrganica::crear($dto->codigo, $dto->nombre, $dto->sigla, $dto->estaActivo)
        );
    }
}
