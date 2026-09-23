<?php
declare(strict_types=1);

use App\Modules\Expedientes\Application\UseCases\EliminarExpedienteUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteConItseError;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteConLicenciaError;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->expedientes = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->useCase = new EliminarExpedienteUseCase($this->expedientes);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());
});

it('elimina expediente exitosamente cuando no tiene dependientes', function () {
    $expediente = new Expediente(
        id: 1,
        uuid: 'uuid-1',
        numeroExpediente: 10,
        fechaRecepcion: new DateTimeImmutable('2025-01-02'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 5,
        representanteId: null,
        observaciones: null,
        fechaVencimiento: new DateTimeImmutable('2025-01-09'),
        fechaAlerta: new DateTimeImmutable('2025-01-06'),
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2025-01-02'),
    );

    $this->expedientes->shouldReceive('buscarPorId')->with(1)->once()->andReturn($expediente);
    $this->expedientes->shouldReceive('tieneLicencia')->with(1)->once()->andReturn(false);
    $this->expedientes->shouldReceive('tieneItse')->with(1)->once()->andReturn(false);
    $this->expedientes->shouldReceive('eliminar')->with(1)->once();

    $this->useCase->execute(1);

    expect(true)->toBeTrue();
});

it('lanza ExpedienteNoEncontrado si expediente a eliminar no existe', function () {
    $this->expedientes->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);

    $this->useCase->execute(999);
})->throws(ExpedienteNoEncontrado::class);

it('lanza ExpedienteConLicenciaError si expediente tiene licencia de funcionamiento', function () {
    $expediente = new Expediente(
        id: 1,
        uuid: 'uuid-1',
        numeroExpediente: 10,
        fechaRecepcion: new DateTimeImmutable('2025-01-02'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 5,
        representanteId: null,
        observaciones: null,
        fechaVencimiento: new DateTimeImmutable('2025-01-09'),
        fechaAlerta: new DateTimeImmutable('2025-01-06'),
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2025-01-02'),
    );

    $this->expedientes->shouldReceive('buscarPorId')->with(1)->once()->andReturn($expediente);
    $this->expedientes->shouldReceive('tieneLicencia')->with(1)->once()->andReturn(true);

    $this->useCase->execute(1);
})->throws(ExpedienteConLicenciaError::class);

it('lanza ExpedienteConItseError si expediente tiene ITSE asociado', function () {
    $expediente = new Expediente(
        id: 1,
        uuid: 'uuid-1',
        numeroExpediente: 10,
        fechaRecepcion: new DateTimeImmutable('2025-01-02'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 5,
        representanteId: null,
        observaciones: null,
        fechaVencimiento: new DateTimeImmutable('2025-01-09'),
        fechaAlerta: new DateTimeImmutable('2025-01-06'),
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2025-01-02'),
    );

    $this->expedientes->shouldReceive('buscarPorId')->with(1)->once()->andReturn($expediente);
    $this->expedientes->shouldReceive('tieneLicencia')->with(1)->once()->andReturn(false);
    $this->expedientes->shouldReceive('tieneItse')->with(1)->once()->andReturn(true);

    $this->useCase->execute(1);
})->throws(ExpedienteConItseError::class);
