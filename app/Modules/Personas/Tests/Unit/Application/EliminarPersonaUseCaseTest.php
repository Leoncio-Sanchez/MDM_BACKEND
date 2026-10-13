<?php

declare(strict_types=1);
use App\Modules\Personas\Application\UseCases\EliminarPersonaUseCase;
use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\Exceptions\PersonaNoEncontrada;
use App\Modules\Personas\Domain\Exceptions\PersonaTieneDependientes;
use App\Modules\Personas\Domain\ValueObjects\Sexo;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class); // arranca Laravel: el test usa facades / helpers HTTP

it('elimina persona correctamente', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
        return $callback();
    });

    $persona = new Persona(1, TipoPersona::Natural, Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($persona);
    $repo->shouldReceive('tieneDependientes')->with(1)->once()->andReturn(false);
    $docRepo->shouldReceive('eliminarPorPersona')->with(1)->once();
    $repo->shouldReceive('eliminar')->with(1)->once();

    $useCase = new EliminarPersonaUseCase($repo, $docRepo);
    $useCase->execute(1);
});

it('lanza excepcion si tiene dependientes', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
        return $callback();
    });

    $persona = new Persona(1, TipoPersona::Natural, Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($persona);
    $repo->shouldReceive('tieneDependientes')->with(1)->once()->andReturn(true);

    $useCase = new EliminarPersonaUseCase($repo, $docRepo);
    $useCase->execute(1);
})->throws(PersonaTieneDependientes::class);

it('lanza excepcion si persona no existe al eliminar', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
        return $callback();
    });

    $repo->shouldReceive('buscarPorId')->with(99)->once()->andReturn(null);

    $useCase = new EliminarPersonaUseCase($repo, $docRepo);
    $useCase->execute(99);
})->throws(PersonaNoEncontrada::class);
