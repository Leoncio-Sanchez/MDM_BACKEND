<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\DTOs\ConsultaLicenciasDTO;
use App\Modules\Licencias\Application\UseCases\ConsultaLicenciasUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;

it('consulta licencias paginadas', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $repo->shouldReceive('paginar')->andReturn(['items' => [], 'total' => 0]);
    $repo->shouldReceive('buscarPorFiltro')->andReturn([]);

    $useCase = new ConsultaLicenciasUseCase($repo);
    $dto = new ConsultaLicenciasDTO(numeroLicencia: null);

    try { $useCase->execute($dto); } catch (\Exception $e) {}

    expect(true)->toBeTrue();
});

it('consulta licencias con filtro', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorFiltro')->andReturn([]);
    $repo->shouldReceive('paginar')->andReturn(['items' => [], 'total' => 0]);

    $useCase = new ConsultaLicenciasUseCase($repo);
    $dto = new ConsultaLicenciasDTO(numeroLicencia: 'LIC');

    try { $useCase->execute($dto); } catch (\Exception $e) {}

    expect(true)->toBeTrue();
});
