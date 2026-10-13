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

    /**
     * Registros que dependen de la unidad.
     *
     * @return array{procedimientos: int, usuarios: int}
     */
    public function contarDependencias(int $id): array;
}
