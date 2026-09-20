<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\DTOs\BuscarLicenciasDTO;
use App\Modules\Licencias\Application\UseCases\BuscarLicenciasUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;

it('busca licencias por nombre comercial', function () {
    // Si buscarPorFiltro retorna Entities y luego mapper los mapea a array.
    $licencias = [];

    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorFiltro')
        ->with('NOMBRE_COMERCIAL', 'Restaurante')
        ->once()
        ->andReturn($licencias);

    $useCase = new BuscarLicenciasUseCase($repo);

    $dto = new BuscarLicenciasDTO(filtro: 'NOMBRE_COMERCIAL', valor: 'Restaurante');
    $resultado = $useCase->execute($dto);

    expect(is_array($resultado))->toBeTrue();
});

it('retorna vacío si filtro no retorna resultados', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorFiltro')->once()->andReturn([]);

    $useCase = new BuscarLicenciasUseCase($repo);
    $dto = new BuscarLicenciasDTO(filtro: 'INVENTADO', valor: 'X');

    expect($useCase->execute($dto))->toBeEmpty();
});
