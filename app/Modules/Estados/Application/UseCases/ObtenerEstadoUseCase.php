<?php

declare(strict_types=1);

namespace App\Modules\Estados\Application\UseCases;

use App\Modules\Estados\Domain\Contracts\EstadoRepositoryInterface;
use App\Modules\Estados\Domain\Entities\Estado;
use App\Modules\Estados\Domain\Exceptions\EstadoNoEncontrado;

final readonly class ObtenerEstadoUseCase
{
    public function __construct(
        private EstadoRepositoryInterface $repositorio,
    ) {}

    public function execute(int $id): Estado
    {
        $estado = $this->repositorio->buscarPorId($id);
        if (! $estado) {
            throw new EstadoNoEncontrado("Estado no encontrado con ID: {$id}");
        }

        return $estado;
    }
}
