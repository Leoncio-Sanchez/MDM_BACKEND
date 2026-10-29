<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Application\DTOs\DenegarLicenciaDTO;
use App\Modules\Expedientes\Domain\Contracts\AutorizacionImprocedenteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\Exceptions\DenegatoriaInvalida;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\Exceptions\LicenciaYaEmitidaError;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;

final readonly class DenegarLicenciaUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
        private AutorizacionImprocedenteRepositoryInterface $autorizaciones,
    ) {}

    public function execute(DenegarLicenciaDTO $dto): AutorizacionImprocedente
    {
        $expediente = $this->expedientes->buscarPorId($dto->expedienteId);
        if ($expediente === null) {
            throw ExpedienteNoEncontrado::porId($dto->expedienteId);
        }
        $expediente->asegurarQueNoEstaAnulado();

        if ($this->expedientes->tieneLicencia($dto->expedienteId)) {
            throw LicenciaYaEmitidaError::paraExpediente($expediente->numeroExpediente);
        }
        if (! $this->expedientes->requiereAutorizacion($dto->expedienteId, TipoAutorizacion::LF)) {
            throw DenegatoriaInvalida::noRequiere($expediente->numeroExpediente, TipoAutorizacion::LF);
        }
        // La denegatoria no se borra ni se repite: es la respuesta final al solicitante
        if ($this->autorizaciones->existe($dto->expedienteId, TipoAutorizacion::LF)) {
            throw DenegatoriaInvalida::yaRegistrada($expediente->numeroExpediente, TipoAutorizacion::LF);
        }

        $autorizacion = AutorizacionImprocedente::crear(
            expedienteId: $dto->expedienteId,
            tipo: TipoAutorizacion::LF,
            fechaRechazo: $dto->fechaRechazo,
            documento: $dto->documento,
            observaciones: $dto->observaciones,
            usuarioId: $dto->usuarioId,
        );

        return $this->autorizaciones->crear($autorizacion);
    }
}
