<?php

declare(strict_types=1);

namespace App\Modules\Pide\Tests\Unit\Application;

use App\Modules\Pide\Application\DTOs\ConsultaSunatDTO;
use App\Modules\Pide\Application\UseCases\ConsultarSunatUseCase;
use App\Modules\Pide\Domain\Contracts\SunatClientInterface;
use App\Modules\Pide\Domain\Exceptions\SunatError;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

it('delega al cliente SUNAT con el RUC del DTO', function () {
    $client = Mockery::mock(SunatClientInterface::class);
    $client->shouldReceive('consultarPorRuc')
        ->once()
        ->with('20123456789')
        ->andReturn(['razonSocial' => 'EMPRESA EJEMPLO S.A.C.']);

    $useCase = new ConsultarSunatUseCase($client);
    $resultado = $useCase->execute(new ConsultaSunatDTO('20123456789'));

    expect($resultado)->toBe(['razonSocial' => 'EMPRESA EJEMPLO S.A.C.']);
});

it('propaga la excepción SunatError del cliente', function () {
    $client = Mockery::mock(SunatClientInterface::class);
    $client->shouldReceive('consultarPorRuc')
        ->once()
        ->with('20123456789')
        ->andThrow(SunatError::timeout());

    $useCase = new ConsultarSunatUseCase($client);

    expect(fn () => $useCase->execute(new ConsultaSunatDTO('20123456789')))
        ->toThrow(SunatError::class, 'El servicio SUNAT no respondió en el tiempo esperado.');
});

it('DTO fromArray construye correctamente desde array', function () {
    $dto = ConsultaSunatDTO::fromArray(['ruc' => '20123456789']);
    expect($dto->ruc)->toBe('20123456789');

    $dtoEmpty = ConsultaSunatDTO::fromArray([]);
    expect($dtoEmpty->ruc)->toBe('');
});
