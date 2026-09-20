<?php
use App\Modules\Personas\Application\UseCases\BuscarPersonasUseCase;
use App\Modules\Personas\Application\DTOs\BuscarPersonasDTO;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;

it('busca por filtro NOMBRE', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorFiltro')->with('NOMBRE', 'juan')->once()->andReturn([['id' => 1]]);
    $useCase = new BuscarPersonasUseCase($repo);
    $result = $useCase->execute(new BuscarPersonasDTO('NOMBRE', 'juan'));
    expect($result)->toBe([['id' => 1]]);
});

it('busca por filtro DOCUMENTO', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorFiltro')->with('DOCUMENTO', '1234')->once()->andReturn([['id' => 2]]);
    $useCase = new BuscarPersonasUseCase($repo);
    $result = $useCase->execute(new BuscarPersonasDTO('DOCUMENTO', '1234'));
    expect($result)->toBe([['id' => 2]]);
});

it('retorna vacío si no hay resultados', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorFiltro')->with('ID', '99')->once()->andReturn([]);
    $useCase = new BuscarPersonasUseCase($repo);
    $result = $useCase->execute(new BuscarPersonasDTO('ID', '99'));
    expect($result)->toBe([]);
});
