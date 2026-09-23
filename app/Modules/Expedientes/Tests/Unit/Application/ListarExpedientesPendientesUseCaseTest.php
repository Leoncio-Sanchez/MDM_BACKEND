<?php
declare(strict_types=1);

use App\Modules\Expedientes\Application\UseCases\ListarExpedientesPendientesUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;

beforeEach(function () {
    $this->repo = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->feriados = Mockery::mock(FeriadoReadRepositoryInterface::class);
    $this->calc = new CalculadoraDiasHabilesService($this->feriados);
    $this->useCase = new ListarExpedientesPendientesUseCase($this->repo, $this->calc);
});

afterEach(function () {
    Mockery::close();
});

it('lista pendientes y añade dias_habiles_restantes', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    $this->repo->shouldReceive('listarPendientes')
        ->once()
        ->andReturn([
            [
                'id' => 1,
                'numero_expediente' => 100,
                'fecha_recepcion' => '2025-01-03T10:00:00Z',
                'nombre' => 'Licencia',
                'fecha_vencimiento' => (new DateTimeImmutable('today'))->modify('+5 days')->format('Y-m-d'),
                'fecha_alerta' => '2025-01-08',
                'persona_nombre' => 'JUAN PEREZ',
                'licencia_pendiente' => true,
                'itse_pendiente' => false,
                'mostrar_alerta' => true,
            ],
        ]);

    $resultado = $this->useCase->execute();

    expect($resultado)->toBeArray()
        ->and($resultado)->toHaveCount(1)
        ->and($resultado[0]['id'])->toBe(1)
        ->and($resultado[0])->toHaveKey('dias_habiles_restantes');
});

it('retorna array vacío si no hay pendientes', function () {
    $this->repo->shouldReceive('listarPendientes')
        ->once()
        ->andReturn([]);

    $this->feriados->shouldNotReceive('esFeriado');

    $resultado = $this->useCase->execute();

    expect($resultado)->toBe([]);
});

it('calcula días restantes correctamente para múltiples pendientes', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    $this->repo->shouldReceive('listarPendientes')
        ->once()
        ->andReturn([
            ['id' => 1, 'fecha_vencimiento' => (new DateTimeImmutable('today'))->modify('+2 days')->format('Y-m-d')],
            ['id' => 2, 'fecha_vencimiento' => (new DateTimeImmutable('today'))->modify('+4 days')->format('Y-m-d')],
        ]);

    $resultado = $this->useCase->execute();

    expect($resultado)->toHaveCount(2)
        ->and($resultado[0])->toHaveKey('dias_habiles_restantes')
        ->and($resultado[1])->toHaveKey('dias_habiles_restantes');
});

it('soporta días restantes negativos si el plazo ya venció', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    $this->repo->shouldReceive('listarPendientes')
        ->once()
        ->andReturn([
            ['id' => 1, 'fecha_vencimiento' => (new DateTimeImmutable('today'))->modify('-10 days')->format('Y-m-d')],
        ]);

    $resultado = $this->useCase->execute();

    expect($resultado[0]['dias_habiles_restantes'])->toBeLessThan(0);
});
