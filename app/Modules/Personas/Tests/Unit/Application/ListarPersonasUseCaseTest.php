<?php

declare(strict_types=1);
use App\Modules\Personas\Application\UseCases\ListarPersonasUseCase;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;

it('lista personas con paginacion por defecto', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('listarTodas')->once()->andReturn([]);
    $useCase = new ListarPersonasUseCase($repo);
    $result = $useCase->execute();
    expect($result)->toBe([]);
});

it('lista personas con paginacion custom', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('listarTodas')->once()->andReturn([]);
    $useCase = new ListarPersonasUseCase($repo);
    $result = $useCase->execute();
    expect($result)->toBe([]);
});
