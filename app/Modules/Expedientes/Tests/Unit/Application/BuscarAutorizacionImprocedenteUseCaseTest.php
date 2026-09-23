<?php

declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\BuscarAutorizacionImprocedenteDTO;
use App\Modules\Expedientes\Application\UseCases\BuscarAutorizacionImprocedenteUseCase;
use App\Modules\Expedientes\Domain\Contracts\AutorizacionImprocedenteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;

beforeEach(function () {
    $this->repo = Mockery::mock(AutorizacionImprocedenteRepositoryInterface::class);
    $this->useCase = new BuscarAutorizacionImprocedenteUseCase($this->repo);
});

afterEach(function () {
    Mockery::close();
});

it('retorna la autorizacion improcedente si existe', function () {
    $autorizacion = new AutorizacionImprocedente(
        id: 1,
        expedienteId: 10,
        tipoAutorizacion: TipoAutorizacion::LF,
        fechaRechazo: new DateTimeImmutable('2026-09-20'),
        documento: 'RES-01',
        observaciones: 'Rechazado',
        usuarioId: 2,
        fechaDigitacion: new DateTimeImmutable('2026-09-20 10:00:00'),
    );

    $this->repo->shouldReceive('buscarPorExpedienteYTipo')
        ->with(10, TipoAutorizacion::LF)
        ->once()
        ->andReturn($autorizacion);

    $dto = new BuscarAutorizacionImprocedenteDTO(10, TipoAutorizacion::LF);
    $resultado = $this->useCase->execute($dto);

    expect($resultado)->not->toBeNull()
        ->and($resultado->id)->toBe(1)
        ->and($resultado->tipoAutorizacion)->toBe(TipoAutorizacion::LF);
});

it('retorna null si no existe autorizacion improcedente', function () {
    $this->repo->shouldReceive('buscarPorExpedienteYTipo')
        ->with(10, TipoAutorizacion::ITSE)
        ->once()
        ->andReturnNull();

    $dto = new BuscarAutorizacionImprocedenteDTO(10, TipoAutorizacion::ITSE);
    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toBeNull();
});
