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
        $resultados = $this->personas->buscarPorFiltro($dto->filtro, $dto->valor);

        return array_map(function ($row) {
            if (isset($row['fecha_creacion'])) {
                $row['fecha_creacion'] = str_replace('+00', 'Z', str_replace(' ', 'T', $row['fecha_creacion']));
            }
            if (isset($row['fecha_actualizacion'])) {
                $row['fecha_actualizacion'] = str_replace('+00', 'Z', str_replace(' ', 'T', $row['fecha_actualizacion']));
            }

            return $row;
        }, $resultados);
    }
}
