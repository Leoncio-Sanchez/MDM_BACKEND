<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\DTOs\VerificarExpedienteDTO;
use App\Modules\Licencias\Application\UseCases\VerificarNumeroExpedienteUseCase;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;

it('retorna que el expediente es válido', function () {
    $expediente = (object) ['id' => 1, 'numero_expediente' => 12345];

    $expRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expRepo->shouldReceive('buscarPorNumeroYAnio')
        ->with(12345, 2025)
        ->once()
        ->andReturn($expediente);

    $licRepo = Mockery::mock(LicenciaRepositoryInterface::class);
    $licRepo->shouldReceive('buscarPorExpedienteId')
        ->with(1)
        ->once()
        ->andReturn(null);

    $useCase = new VerificarNumeroExpedienteUseCase($licRepo, $expRepo);
    $dto = new VerificarExpedienteDTO(numero: '12345', anio: '2025');

    $resultado = $useCase->execute($dto);

    expect($resultado['se_puede_emitir_licencia'])->toBeTrue()
        ->and($resultado['expediente_id'])->toBe(1);
});

it('retorna false si el expediente ya tiene licencia', function () {
    $expediente = (object) ['id' => 1, 'numero_expediente' => 12345];
    $licencia = Licencia::crear('uuid', 'LIC-1', 'A', 1);

    $expRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expRepo->shouldReceive('buscarPorNumeroYAnio')
        ->with(12345, 2025)
        ->once()
        ->andReturn($expediente);

    $licRepo = Mockery::mock(LicenciaRepositoryInterface::class);
    $licRepo->shouldReceive('buscarPorExpedienteId')
        ->with(1)
        ->once()
        ->andReturn($licencia);

    $useCase = new VerificarNumeroExpedienteUseCase($licRepo, $expRepo);
    $dto = new VerificarExpedienteDTO(numero: '12345', anio: '2025');

    $resultado = $useCase->execute($dto);

    expect($resultado['se_puede_emitir_licencia'])->toBeFalse();
});

it('retorna que el expediente no existe', function () {
    $expRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expRepo->shouldReceive('buscarPorNumeroYAnio')
        ->with(99999, 2025)
        ->once()
        ->andReturn(null);

    $licRepo = Mockery::mock(LicenciaRepositoryInterface::class);

    $useCase = new VerificarNumeroExpedienteUseCase($licRepo, $expRepo);
    $dto = new VerificarExpedienteDTO(numero: '99999', anio: '2025');

    $resultado = $useCase->execute($dto);

    expect($resultado['se_puede_emitir_licencia'])->toBeFalse();
});
