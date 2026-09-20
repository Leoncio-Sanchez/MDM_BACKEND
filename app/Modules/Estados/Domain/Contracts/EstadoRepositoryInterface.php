<?php

declare(strict_types=1);

namespace App\Modules\Estados\Domain\Contracts;

use App\Modules\Estados\Domain\Entities\Estado;

interface EstadoRepositoryInterface
{
    /** @return array{items: Estado[], total: int} */
    public function paginar(int $pagina, int $porPagina): array;

    public function buscarPorId(int $id): ?Estado;

    public function crear(Estado $estado): Estado;

    public function actualizar(Estado $estado): Estado;

    public function eliminar(int $id): bool;
}
