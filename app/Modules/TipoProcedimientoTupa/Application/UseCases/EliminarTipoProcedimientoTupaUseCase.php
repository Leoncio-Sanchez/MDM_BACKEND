<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Application\UseCases;

use App\Modules\TipoProcedimientoTupa\Domain\Contracts\TipoProcedimientoTupaRepositoryInterface;

final readonly class EliminarTipoProcedimientoTupaUseCase
{
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}

    public function execute(int $id): void
    {
        $this->repository->eliminar($id);
    }
}
