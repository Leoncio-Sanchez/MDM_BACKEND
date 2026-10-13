<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Application\UseCases;

use App\Modules\UnidadesOrganicas\Domain\Contracts\UnidadOrganicaRepositoryInterface;
use App\Modules\UnidadesOrganicas\Domain\Exceptions\UnidadOrganicaEnUso;
use App\Modules\UnidadesOrganicas\Domain\Exceptions\UnidadOrganicaNoEncontrada;

final readonly class EliminarUnidadOrganicaUseCase
{
    public function __construct(
        private UnidadOrganicaRepositoryInterface $repository
    ) {}

    public function execute(int $id): void
    {
        if ($this->repository->buscarPorId($id) === null) {
            throw UnidadOrganicaNoEncontrada::porId($id);
        }

        // Con procedimientos o usuarios asociados no se borra (se perdería la referencia): se desactiva.
        ['procedimientos' => $procedimientos, 'usuarios' => $usuarios] = $this->repository->contarDependencias($id);
        if ($procedimientos > 0 || $usuarios > 0) {
            throw UnidadOrganicaEnUso::porDependencias($procedimientos, $usuarios);
        }

        $this->repository->eliminar($id);
    }
}
