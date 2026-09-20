<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;

final readonly class ListarPersonasUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
    ) {}

    /** @return array{items: \App\Modules\Personas\Domain\Entities\Persona[], total: int} */
    public function execute(int $pagina = 1, int $porPagina = 10): array
    {
        return $this->personas->paginar($pagina, $porPagina);
    }
}
