<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Application\DTOs\ConsultaExpedientesDTO;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;

final readonly class ConsultaExpedientesUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
    ) {}

    /** @return array<int, Expediente> */
    public function execute(ConsultaExpedientesDTO $dto): array
    {
        return $this->expedientes->consultar($dto->numeroExpediente);
    }
}
