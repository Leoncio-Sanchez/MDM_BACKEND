<?php
declare(strict_types=1);

use App\Modules\Expedientes\Application\UseCases\ObtenerExpedienteUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;

uses(Tests\TestCase::class);

it('obtiene expediente existente por ID', function () {
    $expedientes = Mockery::mock(ExpedienteRepositoryInterface::class);
    $useCase = new ObtenerExpedienteUseCase($expedientes);

    $expediente = new Expediente(
        id: 42,
        uuid: 'uuid-42',
        numeroExpediente: 100,
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

    $expedientes->shouldReceive('buscarPorId')->with(42)->once()->andReturn($expediente);

    $resultado = $useCase->execute(42);

    expect($resultado)->toBeInstanceOf(Expediente::class)
        ->and($resultado->id)->toBe(42)
        ->and($resultado->numeroExpediente)->toBe(100);
});

it('lanza ExpedienteNoEncontrado si expediente no existe', function () {
    $expedientes = Mockery::mock(ExpedienteRepositoryInterface::class);
    $useCase = new ObtenerExpedienteUseCase($expedientes);

    $expedientes->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);

    $useCase->execute(999);
})->throws(ExpedienteNoEncontrado::class);
