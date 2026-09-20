<?php

declare(strict_types=1);

use App\Modules\Estados\Application\DTOs\CrearEstadoDTO;
use App\Modules\Estados\Application\UseCases\CrearEstadoUseCase;
use App\Modules\Estados\Domain\Contracts\EstadoRepositoryInterface;
use App\Modules\Estados\Domain\Entities\Estado;

it('crea un estado a traves del caso de uso', function () {
    /** @var Mockery\MockInterface$repo = Mockery::mock(EstadoRepositoryInterface::class);EstadoRepositoryInterface $repo */
    $repo = Mockery::mock(EstadoRepositoryInterface::class);
    $useCase = new CrearEstadoUseCase($repo);
    $dto = new CrearEstadoDTO('Nuevo', true, false);

    $repo->shouldReceive('crear')->once()->andReturn(
        new Estado(1, 'Nuevo', true, false, false)
    );

    $resultado = $useCase->execute($dto);

    expect($resultado->id)->toBe(1)
        ->and($resultado->nombre)->toBe('Nuevo');
});
