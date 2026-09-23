<?php

declare(strict_types=1);

namespace App\Modules\Pide\Application\UseCases;

use App\Modules\Pide\Application\DTOs\ConsultaReniecDTO;
use App\Modules\Pide\Domain\Contracts\ReniecClientInterface;

final readonly class ConsultarReniecUseCase
{
    public function __construct(
        private ReniecClientInterface $reniec,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ConsultaReniecDTO $dto): array
    {
        return $this->reniec->consultarPorDni($dto->dni);
    }
}
