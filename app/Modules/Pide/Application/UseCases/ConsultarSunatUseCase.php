<?php

declare(strict_types=1);

namespace App\Modules\Pide\Application\UseCases;

use App\Modules\Pide\Application\DTOs\ConsultaSunatDTO;
use App\Modules\Pide\Domain\Contracts\SunatClientInterface;

final readonly class ConsultarSunatUseCase
{
    public function __construct(
        private SunatClientInterface $sunat,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ConsultaSunatDTO $dto): array
    {
        return $this->sunat->consultarPorRuc($dto->ruc);
    }
}
