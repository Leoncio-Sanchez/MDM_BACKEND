<?php
declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\CrearExpedienteDTO;
use App\Modules\Expedientes\Application\UseCases\CrearExpedienteUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\TipoProcedimientoTupaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteDuplicadoError;
use App\Modules\Expedientes\Domain\Exceptions\SolicitanteNoValido;
use App\Modules\Expedientes\Domain\Exceptions\TipoProcedimientoNoValido;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->expedientes = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->tipos = Mockery::mock(TipoProcedimientoTupaReadRepositoryInterface::class);
    $this->personas = Mockery::mock(PersonaReadRepositoryInterface::class);
    $this->feriados = Mockery::mock(FeriadoReadRepositoryInterface::class);
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);
    $this->calculadora = new CalculadoraDiasHabilesService($this->feriados);

    $this->useCase = new CrearExpedienteUseCase(
        $this->expedientes,
        $this->tipos,
        $this->personas,
        $this->calculadora,
    );

    DB::shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());
});

it('crea expediente con número autogenerado', function () {
    $dto = new CrearExpedienteDTO(
        numeroExpediente: null,
        fechaRecepcion: new DateTimeImmutable('2025-01-03'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 10,
        usuarioId: 1,
    );

    $tipo = (object) [
        'id' => 1,
        'plazoAtencionDias' => 5,
        'diasAlertaVencimiento' => 3,
    ];

    $solicitante = (object) ['id' => 10];

    $this->tipos->shouldReceive('buscarPorId')->with(1)->once()->andReturn($tipo);
    $this->personas->shouldReceive('buscarPorId')->with(10)->once()->andReturn($solicitante);
    $this->expedientes->shouldReceive('siguienteNumeroEnAnio')->with(2025)->once()->andReturn(42);
    $this->expedientes->shouldReceive('existeNumeroEnAnio')->with(42, 2025)->once()->andReturn(false);

    $fechaVenc = new DateTimeImmutable('2025-01-10');
    $fechaAlerta = new DateTimeImmutable('2025-01-07');

    $this->expedientes->shouldReceive('crear')
        ->once()
        ->andReturnUsing(fn (Expediente $e) => $e);

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toBeInstanceOf(Expediente::class)
        ->and($resultado->numeroExpediente)->toBe(42)
        ->and($resultado->fechaVencimiento->format('Y-m-d'))->toBe('2025-01-10')
        ->and($resultado->fechaAlerta->format('Y-m-d'))->toBe('2025-01-07');
});

it('crea expediente con número especificado manualmente', function () {
    $dto = new CrearExpedienteDTO(
        numeroExpediente: 99,
        fechaRecepcion: new DateTimeImmutable('2025-02-01'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 10,
        usuarioId: 1,
        representanteId: 20,
        observaciones: 'Test observación',
    );

    $tipo = (object) [
        'id' => 1,
        'plazoAtencionDias' => 10,
        'diasAlertaVencimiento' => 2,
    ];

    $this->tipos->shouldReceive('buscarPorId')->with(1)->once()->andReturn($tipo);
    $this->personas->shouldReceive('buscarPorId')->with(10)->once()->andReturn((object) ['id' => 10]);
    $this->personas->shouldReceive('buscarPorId')->with(20)->once()->andReturn((object) ['id' => 20]);
    $this->expedientes->shouldReceive('existeNumeroEnAnio')->with(99, 2025)->once()->andReturn(false);

    $this->expedientes->shouldReceive('crear')->once()->andReturnUsing(fn (Expediente $e) => $e);

    $resultado = $this->useCase->execute($dto);
    expect($resultado->numeroExpediente)->toBe(99)
        ->and($resultado->representanteId)->toBe(20)
        ->and($resultado->observaciones)->toBe('Test observación')
        ->and($resultado->fechaVencimiento->format('Y-m-d'))->toBe('2025-02-14')
        ->and($resultado->fechaAlerta->format('Y-m-d'))->toBe('2025-02-12');
});

it('lanza TipoProcedimientoNoValido si tipo TUPA no existe', function () {
    $dto = new CrearExpedienteDTO(
        numeroExpediente: null,
        fechaRecepcion: new DateTimeImmutable('2025-01-03'),
        tipoProcedimientoTupaId: 999,
        solicitanteId: 10,
        usuarioId: 1,
    );

    $this->tipos->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);

    $this->useCase->execute($dto);
})->throws(TipoProcedimientoNoValido::class);

it('lanza SolicitanteNoValido si solicitante no existe', function () {
    $dto = new CrearExpedienteDTO(
        numeroExpediente: null,
        fechaRecepcion: new DateTimeImmutable('2025-01-03'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 999,
        usuarioId: 1,
    );

    $this->tipos->shouldReceive('buscarPorId')->with(1)->once()->andReturn((object) ['id' => 1]);
    $this->personas->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);

    $this->useCase->execute($dto);
})->throws(SolicitanteNoValido::class);

it('lanza SolicitanteNoValido si representante especificado no existe', function () {
    $dto = new CrearExpedienteDTO(
        numeroExpediente: null,
        fechaRecepcion: new DateTimeImmutable('2025-01-03'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 10,
        usuarioId: 1,
        representanteId: 888,
    );

    $this->tipos->shouldReceive('buscarPorId')->with(1)->once()->andReturn((object) ['id' => 1]);
    $this->personas->shouldReceive('buscarPorId')->with(10)->once()->andReturn((object) ['id' => 10]);
    $this->personas->shouldReceive('buscarPorId')->with(888)->once()->andReturn(null);

    $this->useCase->execute($dto);
})->throws(SolicitanteNoValido::class);

it('lanza ExpedienteDuplicadoError si (numero, anio) ya existe', function () {
    $dto = new CrearExpedienteDTO(
        numeroExpediente: 5,
        fechaRecepcion: new DateTimeImmutable('2025-01-03'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 10,
        usuarioId: 1,
    );

    $this->tipos->shouldReceive('buscarPorId')->with(1)->once()->andReturn((object) ['id' => 1]);
    $this->personas->shouldReceive('buscarPorId')->with(10)->once()->andReturn((object) ['id' => 10]);
    $this->expedientes->shouldReceive('existeNumeroEnAnio')->with(5, 2025)->once()->andReturn(true);

    $this->useCase->execute($dto);
})->throws(ExpedienteDuplicadoError::class);
