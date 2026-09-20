<?php

declare(strict_types=1);

namespace App\Modules\Estados\Application\UseCases;

use App\Modules\Estados\Application\DTOs\CrearEstadoDTO;
use App\Modules\Estados\Domain\Contracts\EstadoRepositoryInterface;
use App\Modules\Estados\Domain\Entities\Estado;

final readonly class CrearEstadoUseCase
{
    public function __construct(
        private EstadoRepositoryInterface $repositorio,
    ) {}

    public function execute(CrearEstadoDTO $dto): Estado
    {
        $estado = Estado::crear(
            nombre: $dto->nombre,
            esParaLf: $dto->esParaLf,
            esParaItse: $dto->esParaItse,
        );

        return $this->repositorio->crear($estado);
    }
}
