<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Application\DTOs\BuscarPersonasDTO;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;

final readonly class BuscarPersonasUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function execute(BuscarPersonasDTO $dto): array
    {
        return $this->personas->buscarPorFiltro($dto->filtro, $dto->valor);
    }
}
