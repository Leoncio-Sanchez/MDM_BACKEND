<?php

declare(strict_types=1);

namespace App\Modules\Giros\Domain\Contracts;

use App\Modules\Giros\Domain\Entities\Giro;

interface GiroRepositoryInterface
{
    /** @return Giro[] */
    public function obtenerTodos(): array;

    public function buscarPorId(int $id): ?Giro;

    public function guardar(Giro $entity): Giro;

    public function actualizar(Giro $entity): Giro;

    public function eliminar(int $id): void;
}
