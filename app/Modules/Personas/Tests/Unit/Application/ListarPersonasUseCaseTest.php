<?php
use App\Modules\Personas\Application\UseCases\ListarPersonasUseCase;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;

it('lista personas con paginacion por defecto', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('paginar')->with(1, 10)->once()->andReturn(['items' => [], 'total' => 0]);
    $useCase = new ListarPersonasUseCase($repo);
    $result = $useCase->execute();
    expect($result)->toBe(['items' => [], 'total' => 0]);
});

it('lista personas con paginacion custom', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('paginar')->with(2, 20)->once()->andReturn(['items' => [], 'total' => 0]);
    $useCase = new ListarPersonasUseCase($repo);
    $result = $useCase->execute(2, 20);
    expect($result)->toBe(['items' => [], 'total' => 0]);
});
