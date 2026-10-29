<?php

declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\DenegarItseDTO;
use App\Modules\Expedientes\Application\UseCases\DenegarItseUseCase;
use App\Modules\Expedientes\Domain\Contracts\AutorizacionImprocedenteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\Exceptions\ItseYaEmitidaError;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;

beforeEach(function () {
    $this->expedientes = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->autorizaciones = Mockery::mock(AutorizacionImprocedenteRepositoryInterface::class);
    $this->useCase = new DenegarItseUseCase($this->expedientes, $this->autorizaciones);
});

afterEach(function () {
    Mockery::close();
});

it('deniega ITSE creando autorizacion improcedente tipo ITSE', function () {
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

    $this->expedientes->shouldReceive('tieneItse')
        ->with(10)
        ->once()
        ->andReturnFalse();

    $this->expedientes->shouldReceive('requiereAutorizacion')->once()->andReturnTrue();
    $this->autorizaciones->shouldReceive('existe')->once()->andReturnFalse();

    $this->autorizaciones->shouldReceive('crear')
        ->once()
        ->withArgs(function (AutorizacionImprocedente $a) {
            return $a->expedienteId === 10
                && $a->tipoAutorizacion === TipoAutorizacion::ITSE
                && $a->documento === 'INFORME-DESFAVORABLE-01'
                && $a->observaciones === 'Falta extintores'
                && $a->usuarioId === 3;
        })
        ->andReturnArg(0);

    $dto = new DenegarItseDTO(
        expedienteId: 10,
        fechaRechazo: new DateTimeImmutable('2026-09-20'),
        documento: 'INFORME-DESFAVORABLE-01',
        observaciones: 'Falta extintores',
        usuarioId: 3,
    );

    $resultado = $this->useCase->execute($dto);

    expect($resultado->expedienteId)->toBe(10)
        ->and($resultado->tipoAutorizacion)->toBe(TipoAutorizacion::ITSE)
        ->and($resultado->documento)->toBe('INFORME-DESFAVORABLE-01');
});

it('lanza ExpedienteNoEncontrado si expediente no existe al denegar ITSE', function () {
    $this->expedientes->shouldReceive('buscarPorId')
        ->with(999)
        ->once()
        ->andReturnNull();

    $dto = new DenegarItseDTO(
        expedienteId: 999,
        fechaRechazo: new DateTimeImmutable('2026-09-20'),
        documento: 'DOC-01',
        observaciones: 'Test',
        usuarioId: 1,
    );

    $this->useCase->execute($dto);
})->throws(ExpedienteNoEncontrado::class);

it('lanza ItseYaEmitidaError si ya tiene ITSE emitida', function () {
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

    $this->expedientes->shouldReceive('tieneItse')
        ->with(10)
        ->once()
        ->andReturnTrue();

    $dto = new DenegarItseDTO(
        expedienteId: 10,
        fechaRechazo: new DateTimeImmutable('2026-09-20'),
        documento: 'DOC-01',
        observaciones: 'Test',
        usuarioId: 1,
    );

    $this->useCase->execute($dto);
})->throws(ItseYaEmitidaError::class);
