<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Application\UseCases;

use App\Modules\TipoProcedimientoTupa\Application\DTOs\ActualizarTipoProcedimientoTupaDTO;
use App\Modules\TipoProcedimientoTupa\Domain\Contracts\TipoProcedimientoTupaRepositoryInterface;
use App\Modules\TipoProcedimientoTupa\Domain\Entities\TipoProcedimientoTupa;
use App\Modules\TipoProcedimientoTupa\Domain\Exceptions\TipoProcedimientoTupaNoEncontrado;

final readonly class ActualizarTipoProcedimientoTupaUseCase
{
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}

    public function execute(int $id, ActualizarTipoProcedimientoTupaDTO $dto): TipoProcedimientoTupa
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new TipoProcedimientoTupaNoEncontrado;
        }
        $updated = $entity->actualizar(
            $dto->codigo,
            $dto->nombre,
            $dto->plazoAtencionDias,
            $dto->diasAlertaVencimiento,
            $dto->estaActivo,
            $dto->requiereLf,
            $dto->requiereItse,
            $dto->unidadOrganicaId
        );

        return $this->repository->actualizar($updated);
    }
}
