<?php

declare(strict_types=1);
use App\Modules\Personas\Application\UseCases\ListarDocumentosPersonaUseCase;
use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\Exceptions\PersonaNoEncontrada;
use App\Modules\Personas\Domain\ValueObjects\Sexo;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use Carbon\CarbonImmutable;

it('lista documentos de una persona', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    $persona = new Persona(1, TipoPersona::Natural, Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', CarbonImmutable::now(), CarbonImmutable::now(), 1);

    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($persona);
    $docRepo->shouldReceive('listarPorPersona')->with(1)->once()->andReturn([]);

    $useCase = new ListarDocumentosPersonaUseCase($repo, $docRepo);
    expect($useCase->execute(1))->toBe([]);
});

it('lanza error si persona no existe al listar documentos', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);

    $repo->shouldReceive('buscarPorId')->with(99)->once()->andReturn(null);

    $useCase = new ListarDocumentosPersonaUseCase($repo, $docRepo);
    $useCase->execute(99);
})->throws(PersonaNoEncontrada::class);
