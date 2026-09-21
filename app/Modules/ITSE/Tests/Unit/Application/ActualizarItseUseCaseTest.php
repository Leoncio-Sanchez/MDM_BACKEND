<?php
declare(strict_types=1);

use App\Modules\ITSE\Application\DTOs\ActualizarItseDTO;
use App\Modules\ITSE\Application\UseCases\ActualizarItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\NivelRiesgoReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->itses = Mockery::mock(ItseRepositoryInterface::class);
    $this->personas = Mockery::mock(PersonaReadRepositoryInterface::class);
    $this->niveles = Mockery::mock(NivelRiesgoReadRepositoryInterface::class);
    $this->useCase = new ActualizarItseUseCase($this->itses, $this->personas, $this->niveles);
    
    DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
        return $callback();
    });
});

it('actualiza un ITSE existente', function () {
    $dto = new ActualizarItseDTO(1, 123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 100, 'R123', null, false, 1, 1, 1, 1);
    $itse = Itse::crear('uuid', 123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 100, 'R123', null, false, 1, 1, null, 1, 1, 1, 1, CarbonImmutable::now());
    
    $this->itses->shouldReceive('buscarPorId')->with(1)->once()->andReturn($itse);
    $this->itses->shouldReceive('existeNumero')->with(123, 1)->once()->andReturn(false);
    $this->itses->shouldReceive('existeRecibo')->with('R123', 1)->once()->andReturn(false);
    $this->personas->shouldReceive('existe')->with(1)->twice()->andReturn(true);
    $this->niveles->shouldReceive('existe')->with(1)->once()->andReturn(true);
    
    $this->itses->shouldReceive('actualizar')->once()->andReturnUsing(fn(Itse $i) => $i);
    
    $result = $this->useCase->execute($dto);
    expect($result)->toBeInstanceOf(Itse::class);
});

it('lanza ItseNoEncontrado si no existe al actualizar', function () {
    $dto = new ActualizarItseDTO(1, 123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 100, 'R123', null, false, 1, 1, 1, 1);
    
    $this->itses->shouldReceive('buscarPorId')->with(1)->once()->andReturn(null);
    
    $this->useCase->execute($dto);
})->throws(ItseNoEncontrado::class);
