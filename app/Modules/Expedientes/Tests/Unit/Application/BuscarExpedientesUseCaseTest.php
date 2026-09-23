<?php

declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\BuscarExpedientesDTO;
use App\Modules\Expedientes\Application\UseCases\BuscarExpedientesUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;

beforeEach(function () {
    $this->repo = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->feriados = Mockery::mock(FeriadoReadRepositoryInterface::class);
    $this->calc = new CalculadoraDiasHabilesService($this->feriados);
    $this->useCase = new BuscarExpedientesUseCase($this->repo, $this->calc);
});

afterEach(function () {
    Mockery::close();
});

it('busca con filtro válido y añade dias_habiles_restantes', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    $dto = new BuscarExpedientesDTO('NUMERO', '1234');

    $this->repo->shouldReceive('buscarPorFiltro')
        ->once()
        ->with('NUMERO', '1234')
        ->andReturn([
            [
                'id' => 1,
                'numero_expediente' => 1234,
                'fecha_vencimiento' => (new DateTimeImmutable('today'))->modify('+3 days')->format('Y-m-d'),
                'licencia_pendiente' => true,
                'itse_pendiente' => true,
            ],
        ]);

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toHaveCount(1)
        ->and($resultado[0]['id'])->toBe(1)
        ->and($resultado[0])->toHaveKey('dias_habiles_restantes');
});

it('lanza excepción con filtro inválido', function () {
    $dto = new BuscarExpedientesDTO('INVENTADO', 'valor');

    $this->repo->shouldNotReceive('buscarPorFiltro');
    $this->feriados->shouldNotReceive('esFeriado');

    expect(fn () => $this->useCase->execute($dto))
        ->toThrow(InvalidArgumentException::class, "Filtro 'INVENTADO' no válido");
});

it('normaliza filtro en minúsculas y con espacios a mayúsculas', function () {
    $dto = new BuscarExpedientesDTO('  numero  ', '555');

    $this->repo->shouldReceive('buscarPorFiltro')
        ->once()
        ->with('NUMERO', '555')
        ->andReturn([]);

    $this->feriados->shouldNotReceive('esFeriado');

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toBe([]);
});

it('acepta todos los 6 filtros de la lista blanca', function (string $filtro) {
    $dto = new BuscarExpedientesDTO($filtro, 'test');

    $this->repo->shouldReceive('buscarPorFiltro')
        ->once()
        ->with($filtro, 'test')
        ->andReturn([]);

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toBe([]);
})->with([
    'ID',
    'NUMERO',
    'FECHA_RECEPCION',
    'FECHA_VENCIMIENTO',
    'NOMBRE_SOLICITANTE',
    'RUC_SOLICITANTE',
]);

it('retorna array vacío si el repositorio no encuentra resultados', function () {
    $dto = new BuscarExpedientesDTO('RUC_SOLICITANTE', '20123456789');

    $this->repo->shouldReceive('buscarPorFiltro')
        ->once()
        ->with('RUC_SOLICITANTE', '20123456789')
        ->andReturn([]);

    $this->feriados->shouldNotReceive('esFeriado');

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toBe([]);
});
