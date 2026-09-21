<?php
declare(strict_types=1);

use App\Modules\ITSE\Application\DTOs\InactivarItseDTO;
use App\Modules\ITSE\Application\UseCases\InactivarItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseEstadoRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ItseInvalido;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
        return $callback();
    });
});

it('inactiva un ITSE correctamente', function () {
    $itses = Mockery::mock(ItseRepositoryInterface::class);
    $estados = Mockery::mock(ItseEstadoRepositoryInterface::class);
    $dto = new InactivarItseDTO(1, 'Obs', 1);
    
    $itse = Itse::crear('uuid', 123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 100, 'R123', null, false, 1, 1, null, 1, 1, 1, 1, CarbonImmutable::now());
    
    $itses->shouldReceive('buscarPorId')->with(1)->twice()->andReturn($itse);
    $estados->shouldReceive('tieneEstado')->with(1, 'INACTIVO')->once()->andReturn(false);
    $estados->shouldReceive('insertar')->once();
    
    $useCase = new InactivarItseUseCase($itses, $estados);
    $result = $useCase->execute($dto);
    
    expect($result)->toBeInstanceOf(Itse::class);
});

it('lanza ItseInvalido si ya está inactivo', function () {
    $itses = Mockery::mock(ItseRepositoryInterface::class);
    $estados = Mockery::mock(ItseEstadoRepositoryInterface::class);
    $dto = new InactivarItseDTO(1, 'Obs', 1);
    
    $itse = Itse::crear('uuid', 123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 100, 'R123', null, false, 1, 1, null, 1, 1, 1, 1, CarbonImmutable::now());
    
    $itses->shouldReceive('buscarPorId')->with(1)->once()->andReturn($itse);
    $estados->shouldReceive('tieneEstado')->with(1, 'INACTIVO')->once()->andReturn(true);
    
    $useCase = new InactivarItseUseCase($itses, $estados);
    $useCase->execute($dto);
})->throws(ItseInvalido::class);
