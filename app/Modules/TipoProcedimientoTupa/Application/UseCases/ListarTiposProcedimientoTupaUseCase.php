<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Application\UseCases;

use App\Modules\TipoProcedimientoTupa\Domain\Contracts\TipoProcedimientoTupaRepositoryInterface;
use App\Modules\TipoProcedimientoTupa\Domain\Entities\TipoProcedimientoTupa;

final readonly class ListarTiposProcedimientoTupaUseCase
{
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}

    /** @return TipoProcedimientoTupa[] */
    public function execute(): array
    {
        return $this->repository->obtenerTodos();
    }
}
