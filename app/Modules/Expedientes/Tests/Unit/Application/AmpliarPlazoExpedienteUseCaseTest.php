<?php

declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\AmpliarPlazoExpedienteDTO;
use App\Modules\Expedientes\Application\UseCases\AmpliarPlazoExpedienteUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\TipoProcedimientoTupaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\Exceptions\TipoProcedimientoNoValido;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;

beforeEach(function () {
    $this->repo = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->tupa = Mockery::mock(TipoProcedimientoTupaReadRepositoryInterface::class);
    $this->feriados = Mockery::mock(FeriadoReadRepositoryInterface::class);
    $this->feriados->shouldReceive('esFeriado')->andReturn(false)->byDefault();
    $this->calculadora = new CalculadoraDiasHabilesService($this->feriados);
    $this->useCase = new AmpliarPlazoExpedienteUseCase($this->repo, $this->tupa, $this->calculadora);
});

afterEach(function () {
    Mockery::close();
});

it('amplia plazo de expediente correctamente recalculando vencimiento y alerta', function () {
    $fechaRecepcion = new DateTimeImmutable('2026-09-01');
    $fechaVencimientoActual = new DateTimeImmutable('2026-09-15');
    $fechaAlertaActual = new DateTimeImmutable('2026-09-12');
    $fechaSuspension = new DateTimeImmutable('2026-09-10');

    $expedienteOriginal = new Expediente(
        id: 1,
        uuid: 'uuid-1',
        numeroExpediente: 100,
        fechaRecepcion: $fechaRecepcion,
        tipoProcedimientoTupaId: 5,
        solicitanteId: 10,
        representanteId: null,
        observaciones: null,
        fechaVencimiento: $fechaVencimientoActual,
        fechaAlerta: $fechaAlertaActual,
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: $fechaRecepcion,
        autorizacionesImprocedentes: [],
    );

    $this->repo->shouldReceive('buscarPorId')
        ->with(1)
        ->once()
        ->andReturn($expedienteOriginal);

    $this->tupa->shouldReceive('buscarPorId')
        ->with(5)
        ->once()
        ->andReturn((object) [
            'id' => 5,
            'dias_alerta_vencimiento' => 2,
        ]);

    $this->repo->shouldReceive('actualizar')
        ->once()
        ->withArgs(function (Expediente $e) use ($fechaSuspension) {
            return $e->id === 1
                && $e->diasAmpliacion === 5
                && $e->motivoAmpliacion === 'Falta informe técnico'
                && $e->usuarioAmpliacionId === 99
                && $e->fechaSuspension == $fechaSuspension
                && $e->fechaVencimiento > new DateTimeImmutable('2026-09-15');
        })
        ->andReturnArg(0);

    $dto = new AmpliarPlazoExpedienteDTO(
        expedienteId: 1,
        fechaSuspension: $fechaSuspension,
        diasAmpliacion: 5,
        motivoAmpliacion: 'Falta informe técnico',
        usuarioId: 99,
    );

    $resultado = $this->useCase->execute($dto);

    expect($resultado->diasAmpliacion)->toBe(5)
        ->and($resultado->motivoAmpliacion)->toBe('Falta informe técnico')
        ->and($resultado->usuarioAmpliacionId)->toBe(99);
});

it('lanza ExpedienteNoEncontrado si el expediente no existe', function () {
    $this->repo->shouldReceive('buscarPorId')
        ->with(999)
        ->once()
        ->andReturnNull();

    $dto = new AmpliarPlazoExpedienteDTO(
        expedienteId: 999,
        fechaSuspension: new DateTimeImmutable('2026-09-10'),
        diasAmpliacion: 5,
        motivoAmpliacion: 'Test',
        usuarioId: 1,
    );

    $this->useCase->execute($dto);
})->throws(ExpedienteNoEncontrado::class);

it('lanza TipoProcedimientoNoValido si el procedimiento TUPA no existe', function () {
    $expedienteOriginal = new Expediente(
        id: 1,
        uuid: 'uuid-1',
        numeroExpediente: 100,
        fechaRecepcion: new DateTimeImmutable('2026-09-01'),
        tipoProcedimientoTupaId: 999,
        solicitanteId: 10,
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

    $this->repo->shouldReceive('buscarPorId')
        ->with(1)
        ->once()
        ->andReturn($expedienteOriginal);

    $this->tupa->shouldReceive('buscarPorId')
        ->with(999)
        ->once()
        ->andReturnNull();

    $dto = new AmpliarPlazoExpedienteDTO(
        expedienteId: 1,
        fechaSuspension: new DateTimeImmutable('2026-09-10'),
        diasAmpliacion: 5,
        motivoAmpliacion: 'Test',
        usuarioId: 1,
    );

    $this->useCase->execute($dto);
})->throws(TipoProcedimientoNoValido::class);
