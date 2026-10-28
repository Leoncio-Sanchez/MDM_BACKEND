<?php

declare(strict_types=1);

namespace App\Modules\Pide\Tests\Unit\Application;

use App\Modules\Pide\Application\DTOs\ConsultaReniecDTO;
use App\Modules\Pide\Application\UseCases\ConsultarReniecUseCase;
use App\Modules\Pide\Domain\Contracts\ReniecClientInterface;
use App\Modules\Pide\Domain\Exceptions\ReniecError;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

it('consulta RENIEC con el DNI del DTO y devuelve solo los datos de la persona', function () {
    $client = Mockery::mock(ReniecClientInterface::class);
    $client->shouldReceive('consultarPorDni')
        ->once()
        ->with('12345678')
        ->andReturn(['consultarResponse' => ['return' => ['coResultado' => '0000', 'datosPersona' => [
            'prenombres' => 'JUAN', 'apPrimer' => 'PEREZ', 'apSegundo' => 'LOPEZ', 'foto' => 'BASE64...',
        ]]]]);

    $useCase = new ConsultarReniecUseCase($client);
    $resultado = $useCase->execute(new ConsultaReniecDTO('12345678'));

    expect($resultado->nombres)->toBe('JUAN')
        ->and($resultado->apellidoPaterno)->toBe('PEREZ')
        ->and($resultado->toArray())->not->toHaveKey('foto');
});

it('propaga la excepción ReniecError del cliente', function () {
    $client = Mockery::mock(ReniecClientInterface::class);
    $client->shouldReceive('consultarPorDni')
        ->once()
        ->with('12345678')
        ->andThrow(ReniecError::timeout());

    $useCase = new ConsultarReniecUseCase($client);

    expect(fn () => $useCase->execute(new ConsultaReniecDTO('12345678')))
        ->toThrow(ReniecError::class, 'El servicio RENIEC no respondió en el tiempo esperado.');
});

it('DTO fromArray construye correctamente desde array', function () {
    $dto = ConsultaReniecDTO::fromArray(['dni' => '12345678']);
    expect($dto->dni)->toBe('12345678');

    $dtoEmpty = ConsultaReniecDTO::fromArray([]);
    expect($dtoEmpty->dni)->toBe('');
});
