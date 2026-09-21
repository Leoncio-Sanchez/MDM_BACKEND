<?php
declare(strict_types=1);

use App\Modules\ITSE\Application\DTOs\BuscarItseDTO;
use App\Modules\ITSE\Application\UseCases\BuscarItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;

it('busca ITSE por filtro', function () {
    $repo = Mockery::mock(ItseRepositoryInterface::class);
    $dto = new BuscarItseDTO('NUMERO', '123');
    
    $repo->shouldReceive('buscarPorFiltro')->with('NUMERO', '123')->once()->andReturn([['id' => 1]]);
    
    $useCase = new BuscarItseUseCase($repo);
    $result = $useCase->execute($dto);
    
    expect($result)->toBeArray()->and($result[0]['id'])->toBe(1);
});

it('retorna vacío si no hay resultados', function () {
    $repo = Mockery::mock(ItseRepositoryInterface::class);
    $dto = new BuscarItseDTO('NUMERO', '999');
    
    $repo->shouldReceive('buscarPorFiltro')->with('NUMERO', '999')->once()->andReturn([]);
    
    $useCase = new BuscarItseUseCase($repo);
    $result = $useCase->execute($dto);
    
    expect($result)->toBeArray()->and($result)->toBeEmpty();
});
