<?php

declare(strict_types=1);

namespace App\Modules\Pide\Application\UseCases;

use App\Modules\Pide\Application\DTOs\ConsultaReniecDTO;
use App\Modules\Pide\Application\Mappers\PideMapper;
use App\Modules\Pide\Domain\Contracts\ReniecClientInterface;
use App\Modules\Pide\Domain\ValueObjects\PersonaReniec;

final readonly class ConsultarReniecUseCase
{
    public function __construct(
        private ReniecClientInterface $reniec,
    ) {}

    /** Solo los datos que usa el formulario de Personas (sin foto ni otros datos sensibles). */
    public function execute(ConsultaReniecDTO $dto): PersonaReniec
    {
        return PideMapper::personaDesdeReniec($dto->dni, $this->reniec->consultarPorDni($dto->dni));
    }
}
