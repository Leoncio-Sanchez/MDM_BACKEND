<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Application\UseCases;

use App\Modules\TipoProcedimientoTupa\Domain\Contracts\TipoProcedimientoTupaRepositoryInterface;
use App\Modules\TipoProcedimientoTupa\Domain\Entities\TipoProcedimientoTupa;
use App\Modules\TipoProcedimientoTupa\Domain\Exceptions\TipoProcedimientoTupaNoEncontrado;

final readonly class ObtenerTipoProcedimientoTupaUseCase
{
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}

    public function execute(int $id): TipoProcedimientoTupa
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new TipoProcedimientoTupaNoEncontrado;
        }

        return $entity;
    }
}
