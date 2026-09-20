<?php

declare(strict_types=1);

namespace App\Modules\Estados\Application\UseCases;

use App\Modules\Estados\Application\DTOs\ActualizarEstadoDTO;
use App\Modules\Estados\Domain\Contracts\EstadoRepositoryInterface;
use App\Modules\Estados\Domain\Entities\Estado;
use App\Modules\Estados\Domain\Exceptions\EstadoNoEncontrado;

final readonly class ActualizarEstadoUseCase
{
    public function __construct(
        private EstadoRepositoryInterface $repositorio,
    ) {}

    public function execute(ActualizarEstadoDTO $dto): Estado
    {
        $estado = $this->repositorio->buscarPorId($dto->id);
        if (! $estado) {
            throw new EstadoNoEncontrado("Estado no encontrado con ID: {$dto->id}");
        }

        $estadoActualizado = $estado->actualizar(
            nombre: $dto->nombre,
            esParaLf: $dto->esParaLf,
            esParaItse: $dto->esParaItse,
            estaActivo: $dto->estaActivo,
        );

        return $this->repositorio->actualizar($estadoActualizado);
    }
}
