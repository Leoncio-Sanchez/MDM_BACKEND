<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Application\DTOs\DenegarItseDTO;
use App\Modules\Expedientes\Domain\Contracts\AutorizacionImprocedenteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\Exceptions\ItseYaEmitidaError;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;

final readonly class DenegarItseUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
        private AutorizacionImprocedenteRepositoryInterface $autorizaciones,
    ) {}

    public function execute(DenegarItseDTO $dto): AutorizacionImprocedente
    {
        $expediente = $this->expedientes->buscarPorId($dto->expedienteId);
        if ($expediente === null) {
            throw ExpedienteNoEncontrado::porId($dto->expedienteId);
        }

        if ($this->expedientes->tieneItse($dto->expedienteId)) {
            throw ItseYaEmitidaError::paraExpediente($dto->expedienteId);
        }

        $autorizacion = AutorizacionImprocedente::crear(
            expedienteId: $dto->expedienteId,
            tipo: TipoAutorizacion::ITSE,
            fechaRechazo: $dto->fechaRechazo,
            documento: $dto->documento,
            observaciones: $dto->observaciones,
            usuarioId: $dto->usuarioId,
        );

        return $this->autorizaciones->crear($autorizacion);
    }
}
