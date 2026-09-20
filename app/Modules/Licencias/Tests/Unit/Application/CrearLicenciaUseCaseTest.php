<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\DTOs\CrearLicenciaDTO;
use App\Modules\Licencias\Application\UseCases\CrearLicenciaUseCase;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\ExpedienteNoValido;

it('crea una licencia cuando todos los datos son válidos', function () {
    $expediente = (object) ['id' => 1, 'numero_expediente' => 'EXP-2025-001'];

    $expedienteRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expedienteRepo->shouldReceive('buscarPorId')
        ->with(1)
        ->once()
        ->andReturn($expediente);

    $licenciaRepo = Mockery::mock(LicenciaRepositoryInterface::class);
    $licenciaRepo->shouldReceive('crear')
        ->once()
        ->andReturnUsing(fn (Licencia $l) => new Licencia(
            id: 1,
            uuid: $l->uuid,
            numeroLicencia: $l->numeroLicencia,
            fechaEmision: $l->fechaEmision,
            nombreComercial: $l->nombreComercial,
            titularId: $l->titularId,
            expedienteId: $l->expedienteId,
            tipoLicenciaId: $l->tipoLicenciaId,
            nivelRiesgoId: $l->nivelRiesgoId,
            zonificacionId: $l->zonificacionId,
            estado: $l->estado,
        ));

    $useCase = new CrearLicenciaUseCase($licenciaRepo, $expedienteRepo);

    $dto = new CrearLicenciaDTO(
        numeroLicencia: 'LIC-2025-001',
        nombreComercial: 'Restaurante El Buen Sabor',
        tipoLicenciaId: 1,
        titularId: null,
        expedienteId: 1,
        nivelRiesgoId: null,
        zonificacionId: null,
    );

    $resultado = $useCase->execute($dto);

    expect($resultado)
        ->toBeInstanceOf(Licencia::class)
        ->and($resultado->id)->toBe(1)
        ->and($resultado->numeroLicencia->value)->toBe('LIC-2025-001')
        ->and($resultado->nombreComercial->value)->toBe('Restaurante El Buen Sabor');
});

it('lanza ExpedienteNoValido si el expediente no existe', function () {
    $expedienteRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expedienteRepo->shouldReceive('buscarPorId')
        ->with(999)
        ->once()
        ->andReturn(null);

    $licenciaRepo = Mockery::mock(LicenciaRepositoryInterface::class);
    $licenciaRepo->shouldNotReceive('crear');

    $useCase = new CrearLicenciaUseCase($licenciaRepo, $expedienteRepo);

    $dto = new CrearLicenciaDTO(
        numeroLicencia: 12345,
        nombreComercial: 'Test',
        tipoLicenciaId: 1,
        titularId: null,
        expedienteId: 999,
        nivelRiesgoId: null,
        zonificacionId: null,
    );

    expect(fn () => $useCase->execute($dto))
        ->toThrow(ExpedienteNoValido::class);
});

it('crea sin verificar expediente si expedienteId es null', function () {
    $expedienteRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expedienteRepo->shouldNotReceive('buscarPorId');

    $licenciaRepo = Mockery::mock(LicenciaRepositoryInterface::class);
    $licenciaRepo->shouldReceive('crear')->once()->andReturnUsing(fn ($l) => clone clone $l);

    $useCase = new CrearLicenciaUseCase($licenciaRepo, $expedienteRepo);

    $dto = new CrearLicenciaDTO(
        numeroLicencia: 12345,
        nombreComercial: 'Test',
        tipoLicenciaId: 1,
        titularId: null,
        expedienteId: null,
        nivelRiesgoId: null,
        zonificacionId: null,
    );

    $resultado = $useCase->execute($dto);

    expect($resultado)->toBeInstanceOf(Licencia::class);
});
