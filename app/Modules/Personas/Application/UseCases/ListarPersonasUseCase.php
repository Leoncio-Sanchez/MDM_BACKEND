<?php

declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;

final readonly class ListarPersonasUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
    ) {}

    /** @return Persona[] */
    public function execute(): array
    {
        return $this->personas->listarTodas();
    }
}
