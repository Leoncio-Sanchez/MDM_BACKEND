<?php
declare(strict_types=1);

use App\Modules\ITSE\Application\DTOs\CrearItseDTO;
use App\Modules\ITSE\Application\UseCases\CrearItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\NivelRiesgoReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ExpedienteNoValidoItse;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->itses = Mockery::mock(ItseRepositoryInterface::class);
    $this->expedientes = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $this->personas = Mockery::mock(PersonaReadRepositoryInterface::class);
    $this->niveles = Mockery::mock(NivelRiesgoReadRepositoryInterface::class);
    $this->useCase = new CrearItseUseCase($this->itses, $this->expedientes, $this->personas, $this->niveles);
    
    DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
        return $callback();
    });
});

it('crea un ITSE correctamente', function () {
    $dto = new CrearItseDTO(123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 20, 'R123', null, false, 1, 1, null, 1, 1, 1, 1);
    
    $this->itses->shouldReceive('existeNumero')->with(123)->once()->andReturn(false);
    $this->itses->shouldReceive('existeRecibo')->with('R123')->once()->andReturn(false);
    $this->expedientes->shouldReceive('existe')->with(1)->once()->andReturn(true);
    $this->personas->shouldReceive('existe')->with(1)->twice()->andReturn(true);
    $this->niveles->shouldReceive('existe')->with(1)->once()->andReturn(true);
    
    $this->itses->shouldReceive('crear')->once()->andReturnUsing(fn(Itse $i) => $i);
    
    $result = $this->useCase->execute($dto);
    expect($result)->toBeInstanceOf(Itse::class)
        ->and($result->numeroItse)->toBe(123);
});

it('lanza ExpedienteNoValidoItse si expediente no existe', function () {
    $dto = new CrearItseDTO(123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 20, 'R123', null, false, 1, 1, null, 1, 1, 1, 1);
    
    $this->itses->shouldReceive('existeNumero')->with(123)->once()->andReturn(false);
    $this->itses->shouldReceive('existeRecibo')->with('R123')->once()->andReturn(false);
    $this->expedientes->shouldReceive('existe')->with(1)->once()->andReturn(false);
    
    $this->useCase->execute($dto);
})->throws(ExpedienteNoValidoItse::class);
