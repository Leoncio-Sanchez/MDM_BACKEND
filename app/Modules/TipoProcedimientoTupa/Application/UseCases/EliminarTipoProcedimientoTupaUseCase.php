<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Application\UseCases;

use App\Modules\TipoProcedimientoTupa\Domain\Contracts\TipoProcedimientoTupaRepositoryInterface;
use App\Modules\TipoProcedimientoTupa\Domain\Exceptions\TipoProcedimientoTupaEnUso;
use App\Modules\TipoProcedimientoTupa\Domain\Exceptions\TipoProcedimientoTupaNoEncontrado;

final readonly class EliminarTipoProcedimientoTupaUseCase
{
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}

    public function execute(int $id): void
    {
        if ($this->repository->buscarPorId($id) === null) {
            throw new TipoProcedimientoTupaNoEncontrado();
        }

        // Un procedimiento con expedientes no se borra (se perdería la referencia); se desactiva.
        $expedientes = $this->repository->contarExpedientes($id);
        if ($expedientes > 0) {
            throw new TipoProcedimientoTupaEnUso($expedientes);
        }

        $this->repository->eliminar($id);
    }
}
