<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Application\UseCases;

use App\Modules\TipoProcedimientoTupa\Application\DTOs\CrearTipoProcedimientoTupaDTO;
use App\Modules\TipoProcedimientoTupa\Domain\Contracts\TipoProcedimientoTupaRepositoryInterface;
use App\Modules\TipoProcedimientoTupa\Domain\Entities\TipoProcedimientoTupa;

final readonly class CrearTipoProcedimientoTupaUseCase
{
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}

    public function execute(CrearTipoProcedimientoTupaDTO $dto): TipoProcedimientoTupa
    {
        $entity = TipoProcedimientoTupa::crear(
            $dto->codigo,
            $dto->nombre,
            $dto->plazoAtencionDias,
            $dto->diasAlertaVencimiento,
            $dto->requiereLf,
            $dto->requiereItse,
            $dto->unidadOrganicaId,
            $dto->usuarioId,
            $dto->estaActivo
        );

        return $this->repository->guardar($entity);
    }
}
