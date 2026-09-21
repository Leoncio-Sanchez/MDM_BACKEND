<?php

declare(strict_types=1);
use App\Modules\Personas\Application\UseCases\ObtenerPersonaUseCase;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\Exceptions\PersonaNoEncontrada;
use App\Modules\Personas\Domain\ValueObjects\Sexo;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use Carbon\CarbonImmutable;

it('obtiene una persona correctamente', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $persona = new Persona(1, TipoPersona::Natural, Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($persona);
    $useCase = new ObtenerPersonaUseCase($repo);
    expect($useCase->execute(1))->toBe($persona);
});

it('lanza excepcion si persona no existe', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorId')->with(99)->once()->andReturn(null);
    $useCase = new ObtenerPersonaUseCase($repo);
    $useCase->execute(99);
})->throws(PersonaNoEncontrada::class);
