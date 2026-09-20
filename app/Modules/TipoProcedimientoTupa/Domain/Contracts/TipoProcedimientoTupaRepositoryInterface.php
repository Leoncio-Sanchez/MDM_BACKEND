<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Domain\Contracts;

use App\Modules\TipoProcedimientoTupa\Domain\Entities\TipoProcedimientoTupa;

interface TipoProcedimientoTupaRepositoryInterface
{
    /** @return TipoProcedimientoTupa[] */
    public function obtenerTodos(): array;

    public function buscarPorId(int $id): ?TipoProcedimientoTupa;

    public function guardar(TipoProcedimientoTupa $entity): TipoProcedimientoTupa;

    public function actualizar(TipoProcedimientoTupa $entity): TipoProcedimientoTupa;

    public function eliminar(int $id): void;
}
