<?php

declare(strict_types=1);
use App\Modules\Personas\Application\DTOs\ActualizarPersonaDTO;
use App\Modules\Personas\Application\UseCases\ActualizarPersonaUseCase;
use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\ValueObjects\Sexo;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

it('actualiza persona correctamente sin tocar documentos', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
        return $callback();
    });

    $dto = new ActualizarPersonaDTO(null, null, 'Pedro', null, null, null, null, null, null, null, null, null);
    $persona = new Persona(1, TipoPersona::Natural, Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', CarbonImmutable::now(), CarbonImmutable::now(), 1);

    $repo->shouldReceive('buscarPorId')->with(1)->times(2)->andReturn($persona);
    $repo->shouldReceive('actualizar')->with(Mockery::any())->once()->andReturn($persona);

    $useCase = new ActualizarPersonaUseCase($repo, $docRepo);
    $useCase->execute(1, $dto);
});

it('actualiza persona y reemplaza documentos', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
        return $callback();
    });

    $dto = ActualizarPersonaDTO::fromArray(['nombres' => 'Pedro', 'documentos' => []]);
    $persona = new Persona(1, TipoPersona::Natural, Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', CarbonImmutable::now(), CarbonImmutable::now(), 1);

    $repo->shouldReceive('buscarPorId')->with(1)->times(2)->andReturn($persona);
    $repo->shouldReceive('actualizar')->with(Mockery::any())->once()->andReturn($persona);
    $docRepo->shouldReceive('eliminarPorPersona')->with(1)->once();

    $useCase = new ActualizarPersonaUseCase($repo, $docRepo);
    $useCase->execute(1, $dto);
});
