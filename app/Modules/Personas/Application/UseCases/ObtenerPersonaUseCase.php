<?php

declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\Exceptions\PersonaNoEncontrada;

final readonly class ObtenerPersonaUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
    ) {}

    public function execute(int $id): Persona
    {
        $persona = $this->personas->buscarPorId($id);
        if (! $persona) {
            throw PersonaNoEncontrada::porId($id);
        }

        return $persona;
    }
}
