<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Domain\Contracts;

use App\Modules\UnidadesOrganicas\Domain\Entities\UnidadOrganica;

interface UnidadOrganicaRepositoryInterface
{
    /** @return array<int, UnidadOrganica> */
    public function listar(): array;

    public function buscarPorId(int $id): ?UnidadOrganica;

    public function guardar(UnidadOrganica $unidad): UnidadOrganica;

    public function actualizar(UnidadOrganica $unidad): UnidadOrganica;

    public function eliminar(int $id): void;

    /** Cantidad de procedimientos TUPA que dependen de la unidad. */
    public function contarProcedimientos(int $id): int;
}
