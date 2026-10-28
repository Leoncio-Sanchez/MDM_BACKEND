<?php
declare(strict_types=1);

use App\Modules\ITSE\Application\UseCases\ObtenerItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;
use Carbon\CarbonImmutable;

it('obtiene un ITSE por ID', function () {
    $repo = Mockery::mock(ItseRepositoryInterface::class);
    $itse = Itse::crear('uuid', 123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 20, 'R123', null, false, 1, 1, null, 1, 1, 1, 1, CarbonImmutable::now());
    
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($itse);
    
    $useCase = new ObtenerItseUseCase($repo);
    $result = $useCase->execute(1);
    
    expect($result)->toBeInstanceOf(Itse::class);
});

it('lanza ItseNoEncontrado si no existe', function () {
    $repo = Mockery::mock(ItseRepositoryInterface::class);
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn(null);
    
    $useCase = new ObtenerItseUseCase($repo);
    $useCase->execute(1);
})->throws(ItseNoEncontrado::class);
