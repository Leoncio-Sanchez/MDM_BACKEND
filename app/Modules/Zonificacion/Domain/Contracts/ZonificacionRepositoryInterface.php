<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Domain\Contracts;

use App\Modules\Zonificacion\Domain\Entities\Zonificacion;

interface ZonificacionRepositoryInterface
{
    /** @return Zonificacion[] */
    public function obtenerTodos(): array;

    public function buscarPorId(int $id): ?Zonificacion;

    public function guardar(Zonificacion $zonificacion): Zonificacion;

    public function actualizar(Zonificacion $zonificacion): Zonificacion;

    public function eliminar(int $id): void;
}
