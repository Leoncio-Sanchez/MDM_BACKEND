<?php

declare(strict_types=1);
use App\Modules\Personas\Application\DTOs\ConsultarReniecDTO;
use App\Modules\Personas\Application\DTOs\PersonaReniecDTO;
use App\Modules\Personas\Application\UseCases\ConsultarReniecUseCase;
use App\Modules\Personas\Domain\Contracts\ReniecClientInterface;
use App\Modules\Personas\Domain\Exceptions\DocumentoInvalido;
use App\Modules\Personas\Domain\Exceptions\ReniecNoDisponible;
use Psr\Log\LoggerInterface;

it('consulta RENIEC con DNI válido', function () {
    $reniec = Mockery::mock(ReniecClientInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);
    $dto = new PersonaReniecDTO('12345678', 'Juan', 'Perez', 'Gomez', null, null);

    $logger->shouldReceive('info')->once();
    $reniec->shouldReceive('consultarPorDni')->with('12345678')->once()->andReturn($dto);

    $useCase = new ConsultarReniecUseCase($reniec, $logger);
    expect($useCase->execute(new ConsultarReniecDTO('12345678')))->toBe($dto);
});

it('lanza DocumentoInvalido con DNI inválido', function () {
    $reniec = Mockery::mock(ReniecClientInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    $useCase = new ConsultarReniecUseCase($reniec, $logger);
    $useCase->execute(new ConsultarReniecDTO('123A'));
})->throws(DocumentoInvalido::class);

it('propaga ReniecNoDisponibleError si PIDE falla', function () {
    $reniec = Mockery::mock(ReniecClientInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);

    $logger->shouldReceive('info')->once();
    $logger->shouldReceive('error')->once();
    $reniec->shouldReceive('consultarPorDni')->with('12345678')->once()->andThrow(ReniecNoDisponible::porDni('12345678'));

    $useCase = new ConsultarReniecUseCase($reniec, $logger);
    $useCase->execute(new ConsultarReniecDTO('12345678'));
})->throws(ReniecNoDisponible::class);
