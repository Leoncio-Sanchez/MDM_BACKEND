<?php

declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\ActualizarExpedienteDTO;
use App\Modules\Expedientes\Application\UseCases\ActualizarExpedienteUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\TipoProcedimientoTupaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteDuplicadoError;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\Exceptions\SolicitanteNoValido;
use App\Modules\Expedientes\Domain\Exceptions\TipoProcedimientoNoValido;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->expedientes = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->tipos = Mockery::mock(TipoProcedimientoTupaReadRepositoryInterface::class);
    $this->personas = Mockery::mock(PersonaReadRepositoryInterface::class);
    $this->feriados = Mockery::mock(FeriadoReadRepositoryInterface::class);
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);
    $this->calculadora = new CalculadoraDiasHabilesService($this->feriados);

    $this->useCase = new ActualizarExpedienteUseCase(
        $this->expedientes,
        $this->tipos,
        $this->personas,
        $this->calculadora,
    );

    DB::shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());
});

it('actualiza expediente y recalcula fechas en días hábiles', function () {
    $existente = new Expediente(
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

    $dto = new ActualizarExpedienteDTO(
        expedienteId: 1,
        numeroExpediente: 20,
        fechaRecepcion: new DateTimeImmutable('2025-01-03'),
        tipoProcedimientoTupaId: 2,
        solicitanteId: 6,
        representanteId: 7,
        observaciones: 'Nueva observación',
    );

    $tipo = (object) [
        'id' => 2,
        'plazoAtencionDias' => 5,
        'diasAlertaVencimiento' => 3,
    ];

    $this->expedientes->shouldReceive('buscarPorId')->with(1)->once()->andReturn($existente);
    $this->tipos->shouldReceive('buscarPorId')->with(2)->once()->andReturn($tipo);
    $this->personas->shouldReceive('buscarPorId')->with(6)->once()->andReturn((object) ['id' => 6]);
    $this->personas->shouldReceive('buscarPorId')->with(7)->once()->andReturn((object) ['id' => 7]);
    $this->expedientes->shouldReceive('existeNumeroEnAnio')->with(20, 2025, 1)->once()->andReturn(false);
    $this->expedientes->shouldReceive('actualizar')->once()->andReturnUsing(fn (Expediente $e) => $e);

    $resultado = $this->useCase->execute($dto);

    expect($resultado->numeroExpediente)->toBe(20)
        ->and($resultado->tipoProcedimientoTupaId)->toBe(2)
        ->and($resultado->solicitanteId)->toBe(6)
        ->and($resultado->representanteId)->toBe(7)
        ->and($resultado->observaciones)->toBe('Nueva observación')
        ->and($resultado->fechaVencimiento->format('Y-m-d'))->toBe('2025-01-10')
        ->and($resultado->fechaAlerta->format('Y-m-d'))->toBe('2025-01-07');
});

it('recalcula fechas aplicando ampliación sobre la fecha base cuando existe', function () {
    $existente = new Expediente(
        id: 2,
        uuid: 'uuid-2',
        numeroExpediente: 15,
        fechaRecepcion: new DateTimeImmutable('2025-01-02'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 5,
        representanteId: null,
        observaciones: null,
        fechaVencimiento: new DateTimeImmutable('2025-01-14'),
        fechaAlerta: new DateTimeImmutable('2025-01-09'),
        fechaSuspension: null,
        diasAmpliacion: 2,
        motivoAmpliacion: 'Por peritaje',
        usuarioAmpliacionId: 1,
        fechaDigitacionAmpliacion: new DateTimeImmutable('2025-01-08'),
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2025-01-02'),
    );

    $dto = new ActualizarExpedienteDTO(
        expedienteId: 2,
        numeroExpediente: 15,
        fechaRecepcion: new DateTimeImmutable('2025-01-03'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 5,
    );

    $tipo = (object) [
        'id' => 1,
        'plazoAtencionDias' => 5,
        'diasAlertaVencimiento' => 3,
    ];

    $this->expedientes->shouldReceive('buscarPorId')->with(2)->once()->andReturn($existente);
    $this->tipos->shouldReceive('buscarPorId')->with(1)->once()->andReturn($tipo);
    $this->personas->shouldReceive('buscarPorId')->with(5)->once()->andReturn((object) ['id' => 5]);
    $this->expedientes->shouldReceive('existeNumeroEnAnio')->with(15, 2025, 2)->once()->andReturn(false);
    $this->expedientes->shouldReceive('actualizar')->once()->andReturnUsing(fn (Expediente $e) => $e);

    $resultado = $this->useCase->execute($dto);

    // Base = 2025-01-10 + 2 días hábiles -> 2025-01-14. Alerta -3 días hábiles -> 2025-01-09
    expect($resultado->fechaVencimiento->format('Y-m-d'))->toBe('2025-01-14')
        ->and($resultado->fechaAlerta->format('Y-m-d'))->toBe('2025-01-09');
});

it('lanza ExpedienteNoEncontrado si expediente no existe', function () {
    $dto = new ActualizarExpedienteDTO(
        expedienteId: 999,
        numeroExpediente: 10,
        fechaRecepcion: new DateTimeImmutable('2025-01-03'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 5,
    );

    $this->expedientes->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);

    $this->useCase->execute($dto);
})->throws(ExpedienteNoEncontrado::class);

it('lanza TipoProcedimientoNoValido si tipo TUPA no existe', function () {
    $existente = Expediente::crear('uuid-1', 1, new DateTimeImmutable, 1, 1, 1, new DateTimeImmutable, new DateTimeImmutable);

    $dto = new ActualizarExpedienteDTO(1, 1, new DateTimeImmutable, 999, 1);

    $this->expedientes->shouldReceive('buscarPorId')->with(1)->once()->andReturn($existente);
    $this->tipos->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);

    $this->useCase->execute($dto);
})->throws(TipoProcedimientoNoValido::class);

it('lanza SolicitanteNoValido si solicitante no existe', function () {
    $existente = Expediente::crear('uuid-1', 1, new DateTimeImmutable, 1, 1, 1, new DateTimeImmutable, new DateTimeImmutable);

    $dto = new ActualizarExpedienteDTO(1, 1, new DateTimeImmutable, 1, 999);

    $this->expedientes->shouldReceive('buscarPorId')->with(1)->once()->andReturn($existente);
    $this->tipos->shouldReceive('buscarPorId')->with(1)->once()->andReturn((object) ['id' => 1]);
    $this->personas->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);

    $this->useCase->execute($dto);
})->throws(SolicitanteNoValido::class);

it('lanza SolicitanteNoValido si representante no existe', function () {
    $existente = Expediente::crear('uuid-1', 1, new DateTimeImmutable, 1, 1, 1, new DateTimeImmutable, new DateTimeImmutable);

    $dto = new ActualizarExpedienteDTO(1, 1, new DateTimeImmutable, 1, 5, 888);

    $this->expedientes->shouldReceive('buscarPorId')->with(1)->once()->andReturn($existente);
    $this->tipos->shouldReceive('buscarPorId')->with(1)->once()->andReturn((object) ['id' => 1]);
    $this->personas->shouldReceive('buscarPorId')->with(5)->once()->andReturn((object) ['id' => 5]);
    $this->personas->shouldReceive('buscarPorId')->with(888)->once()->andReturn(null);

    $this->useCase->execute($dto);
})->throws(SolicitanteNoValido::class);

it('lanza ExpedienteDuplicadoError si numero en anio ya existe en otro expediente', function () {
    $existente = Expediente::crear('uuid-1', 1, new DateTimeImmutable('2025-01-01'), 1, 1, 1, new DateTimeImmutable, new DateTimeImmutable);

    $dto = new ActualizarExpedienteDTO(1, 99, new DateTimeImmutable('2025-01-01'), 1, 1);

    $this->expedientes->shouldReceive('buscarPorId')->with(1)->once()->andReturn($existente);
    $this->tipos->shouldReceive('buscarPorId')->with(1)->once()->andReturn((object) ['id' => 1]);
    $this->personas->shouldReceive('buscarPorId')->with(1)->once()->andReturn((object) ['id' => 1]);
    $this->expedientes->shouldReceive('existeNumeroEnAnio')->with(99, 2025, 1)->once()->andReturn(true);

    $this->useCase->execute($dto);
})->throws(ExpedienteDuplicadoError::class);
