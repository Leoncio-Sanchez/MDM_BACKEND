<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Application\DTOs\BuscarAutorizacionImprocedenteDTO;
use App\Modules\Expedientes\Domain\Contracts\AutorizacionImprocedenteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;

final readonly class BuscarAutorizacionImprocedenteUseCase
{
    public function __construct(
        private AutorizacionImprocedenteRepositoryInterface $autorizaciones,
    ) {}

    public function execute(BuscarAutorizacionImprocedenteDTO $dto): ?AutorizacionImprocedente
    {
        return $this->autorizaciones->buscarPorExpedienteYTipo(
            $dto->expedienteId,
            $dto->tipo,
        );
    }
}
