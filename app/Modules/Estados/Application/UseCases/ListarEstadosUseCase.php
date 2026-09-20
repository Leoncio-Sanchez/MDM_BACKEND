<?php

declare(strict_types=1);

namespace App\Modules\Estados\Application\UseCases;

use App\Modules\Estados\Domain\Contracts\EstadoRepositoryInterface;
use App\Modules\Estados\Domain\Entities\Estado;

final readonly class ListarEstadosUseCase
{
    public function __construct(
        private EstadoRepositoryInterface $repositorio,
    ) {}

    /** @return array{items: Estado[], total: int} */
    public function execute(int $pagina, int $porPagina): array
    {
        return $this->repositorio->paginar($pagina, $porPagina);
    }
}
