<?php
declare(strict_types=1);

use App\Modules\ITSE\Application\DTOs\CrearItseDTO;
use App\Modules\ITSE\Application\UseCases\CrearItseUseCase;
use App\Modules\ITSE\Domain\Contracts\AutorizacionImprocedenteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\InspeccionReadRepositoryInterface;
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
    // Sin acta exigible: estos casos prueban las demás validaciones
    $this->inspecciones = Mockery::mock(InspeccionReadRepositoryInterface::class);
    $this->inspecciones->shouldReceive('situacion')->andReturn(null);
    // Sin denegatoria registrada, salvo en el caso que la prueba
    $this->improcedentes = Mockery::mock(AutorizacionImprocedenteReadRepositoryInterface::class);
    $this->improcedentes->shouldReceive('existe')->with(Mockery::any(), 'ITSE')->andReturn(false)->byDefault();
    $this->useCase = new CrearItseUseCase($this->itses, $this->expedientes, $this->personas, $this->niveles, $this->inspecciones, $this->improcedentes);
    
    DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
        return $callback();
    });
});

it('crea un ITSE correctamente', function () {
    $dto = new CrearItseDTO(123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 20, 'R123', null, false, 1, 1, null, 1, 1, 1, 1);
    
    $this->itses->shouldReceive('existeNumero')->with(123)->once()->andReturn(false);
    $this->itses->shouldReceive('existeRecibo')->with('R123')->once()->andReturn(false);
    $this->expedientes->shouldReceive('existe')->with(1)->once()->andReturn(true);
    $this->expedientes->shouldReceive('estaAnulado')->with(1)->once()->andReturn(false);
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

it('rechaza el certificado si el expediente tiene la denegatoria de ITSE y no lo crea', function () {
    $dto = new CrearItseDTO(123, CarbonImmutable::now(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 20, 'R123', null, false, 1, 1, null, 1, 1, 1, 1);

    $this->itses->shouldReceive('existeNumero')->andReturn(false);
    $this->itses->shouldReceive('existeRecibo')->andReturn(false);
    $this->expedientes->shouldReceive('existe')->with(1)->andReturn(true);
    $this->expedientes->shouldReceive('estaAnulado')->with(1)->andReturn(false);
    $this->improcedentes->shouldReceive('existe')->with(1, 'ITSE')->andReturn(true);
    $this->itses->shouldNotReceive('crear');

    expect(fn () => $this->useCase->execute($dto))
        ->toThrow(ExpedienteNoValidoItse::class, 'denegatoria de ITSE');
});
