<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;

final readonly class ListarExpedientesUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
    ) {}

    /**
     * @return array{items: array<int, Expediente>, total: int}
     */
    public function execute(int $pagina = 1, int $porPagina = 15): array
    {
        return $this->expedientes->paginar($pagina, $porPagina);
    }
}
