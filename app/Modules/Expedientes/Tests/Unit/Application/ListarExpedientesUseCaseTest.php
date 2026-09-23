<?php

declare(strict_types=1);

use App\Modules\Expedientes\Application\UseCases\ListarExpedientesUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use Tests\TestCase;

uses(TestCase::class);

it('retorna lista paginada de expedientes', function () {
    $expedientes = Mockery::mock(ExpedienteRepositoryInterface::class);
    $useCase = new ListarExpedientesUseCase($expedientes);

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

    $expedientes->shouldReceive('paginar')
        ->with(1, 15)
        ->once()
        ->andReturn([
            'items' => [$expediente],
            'total' => 1,
        ]);

    $resultado = $useCase->execute(1, 15);

    expect($resultado)->toHaveKeys(['items', 'total'])
        ->and($resultado['total'])->toBe(1)
        ->and($resultado['items'])->toHaveCount(1)
        ->and($resultado['items'][0]->numeroExpediente)->toBe(10);
});
