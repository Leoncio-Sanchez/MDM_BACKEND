<?php
declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\ConsultaExpedientesDTO;
use App\Modules\Expedientes\Application\UseCases\ConsultaExpedientesUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\ValueObjects\NumeroExpediente;
use App\Modules\Expedientes\Domain\ValueObjects\PlazoExpediente;

beforeEach(function () {
    $this->repo = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->useCase = new ConsultaExpedientesUseCase($this->repo);

    $this->expedienteMock = Expediente::crear(
        uuid: 'test-uuid-1234',
        numeroExpediente: 777,
        fechaRecepcion: new DateTimeImmutable('2025-01-03 10:00:00'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-01-10'),
        fechaAlerta: new DateTimeImmutable('2025-01-08'),
    );
});

afterEach(function () {
    Mockery::close();
});

it('consulta todos los expedientes si numeroExpediente es null', function () {
    $dto = new ConsultaExpedientesDTO(null);

    $this->repo->shouldReceive('consultar')
        ->once()
        ->with(null)
        ->andReturn([$this->expedienteMock]);

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toHaveCount(1)
        ->and($resultado[0]->numeroExpediente)->toBe(777);
});

it('filtra por numeroExpediente cuando se proporciona', function () {
    $dto = new ConsultaExpedientesDTO(777);

    $this->repo->shouldReceive('consultar')
        ->once()
        ->with(777)
        ->andReturn([$this->expedienteMock]);

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toHaveCount(1)
        ->and($resultado[0]->numeroExpediente)->toBe(777);
});

it('retorna array vacío si no hay coincidencias', function () {
    $dto = new ConsultaExpedientesDTO(999999);

    $this->repo->shouldReceive('consultar')
        ->once()
        ->with(999999)
        ->andReturn([]);

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toBe([]);
});
