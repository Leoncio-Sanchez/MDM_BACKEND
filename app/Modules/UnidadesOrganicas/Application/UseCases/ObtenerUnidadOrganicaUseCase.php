<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Application\UseCases;

use App\Modules\UnidadesOrganicas\Domain\Contracts\UnidadOrganicaRepositoryInterface;
use App\Modules\UnidadesOrganicas\Domain\Entities\UnidadOrganica;
use App\Modules\UnidadesOrganicas\Domain\Exceptions\UnidadOrganicaNoEncontrada;

final readonly class ObtenerUnidadOrganicaUseCase
{
    public function __construct(
        private UnidadOrganicaRepositoryInterface $repository
    ) {}

    public function execute(int $id): UnidadOrganica
    {
        return $this->repository->buscarPorId($id) ?? throw UnidadOrganicaNoEncontrada::porId($id);
    }
}
