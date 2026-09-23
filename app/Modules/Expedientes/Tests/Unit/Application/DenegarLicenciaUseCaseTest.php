<?php

declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\DenegarLicenciaDTO;
use App\Modules\Expedientes\Application\UseCases\DenegarLicenciaUseCase;
use App\Modules\Expedientes\Domain\Contracts\AutorizacionImprocedenteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\Exceptions\LicenciaYaEmitidaError;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;

beforeEach(function () {
    $this->expedientes = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->autorizaciones = Mockery::mock(AutorizacionImprocedenteRepositoryInterface::class);
    $this->useCase = new DenegarLicenciaUseCase($this->expedientes, $this->autorizaciones);
});

afterEach(function () {
    Mockery::close();
});

it('deniega licencia creando autorizacion improcedente tipo LF', function () {
    $expediente = new Expediente(
        id: 10,
        uuid: 'uuid-10',
        numeroExpediente: 100,
        fechaRecepcion: new DateTimeImmutable('2026-09-01'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        representanteId: null,
        observaciones: null,
        fechaVencimiento: new DateTimeImmutable('2026-09-15'),
        fechaAlerta: new DateTimeImmutable('2026-09-12'),
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2026-09-01'),
        autorizacionesImprocedentes: [],
    );

    $this->expedientes->shouldReceive('buscarPorId')
        ->with(10)
        ->once()
        ->andReturn($expediente);

    $this->expedientes->shouldReceive('tieneLicencia')
        ->with(10)
        ->once()
        ->andReturnFalse();

    $this->autorizaciones->shouldReceive('crear')
        ->once()
        ->withArgs(function (AutorizacionImprocedente $a) {
            return $a->expedienteId === 10
                && $a->tipoAutorizacion === TipoAutorizacion::LF
                && $a->documento === 'RES-DENEGADA-01'
                && $a->observaciones === 'No cumple zonificación'
                && $a->usuarioId === 5;
        })
        ->andReturnArg(0);

    $dto = new DenegarLicenciaDTO(
        expedienteId: 10,
        fechaRechazo: new DateTimeImmutable('2026-09-20'),
        documento: 'RES-DENEGADA-01',
        observaciones: 'No cumple zonificación',
        usuarioId: 5,
    );

    $resultado = $this->useCase->execute($dto);

    expect($resultado->expedienteId)->toBe(10)
        ->and($resultado->tipoAutorizacion)->toBe(TipoAutorizacion::LF)
        ->and($resultado->documento)->toBe('RES-DENEGADA-01');
});

it('lanza ExpedienteNoEncontrado si expediente no existe al denegar licencia', function () {
    $this->expedientes->shouldReceive('buscarPorId')
        ->with(999)
        ->once()
        ->andReturnNull();

    $dto = new DenegarLicenciaDTO(
        expedienteId: 999,
        fechaRechazo: new DateTimeImmutable('2026-09-20'),
        documento: 'DOC-01',
        observaciones: 'Test',
        usuarioId: 1,
    );

    $this->useCase->execute($dto);
})->throws(ExpedienteNoEncontrado::class);

it('lanza LicenciaYaEmitidaError si ya tiene licencia emitida', function () {
    $expediente = new Expediente(
        id: 10,
        uuid: 'uuid-10',
        numeroExpediente: 100,
        fechaRecepcion: new DateTimeImmutable('2026-09-01'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        representanteId: null,
        observaciones: null,
        fechaVencimiento: new DateTimeImmutable('2026-09-15'),
        fechaAlerta: new DateTimeImmutable('2026-09-12'),
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2026-09-01'),
        autorizacionesImprocedentes: [],
    );

    $this->expedientes->shouldReceive('buscarPorId')
        ->with(10)
        ->once()
        ->andReturn($expediente);

    $this->expedientes->shouldReceive('tieneLicencia')
        ->with(10)
        ->once()
        ->andReturnTrue();

    $dto = new DenegarLicenciaDTO(
        expedienteId: 10,
        fechaRechazo: new DateTimeImmutable('2026-09-20'),
        documento: 'DOC-01',
        observaciones: 'Test',
        usuarioId: 1,
    );

    $this->useCase->execute($dto);
})->throws(LicenciaYaEmitidaError::class);
