<?php
use App\Modules\Personas\Application\UseCases\CrearPersonaUseCase;
use App\Modules\Personas\Application\DTOs\CrearPersonaDTO;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use App\Modules\Personas\Domain\ValueObjects\Sexo;
use Illuminate\Support\Facades\DB;

it('crea una persona correctamente', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) { return $callback(); });
    
    $dto = new CrearPersonaDTO(TipoPersona::Natural, Sexo::Masculino, 'Juan', 'Dir 1', 'Dist 1', 'Prov 1', 'Dep 1', 1, 'Perez', 'Gomez', null, null, []);
    $personaFake = new Persona(1, TipoPersona::Natural, Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', \Carbon\CarbonImmutable::now(), \Carbon\CarbonImmutable::now(), 1);
    
    $repo->shouldReceive('crear')->with(Mockery::any())->once()->andReturn($personaFake);
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($personaFake);
    
    $useCase = new CrearPersonaUseCase($repo, $docRepo);
    $result = $useCase->execute($dto);
    expect($result)->toBe($personaFake);
});

it('crea persona con documentos asociados', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) { return $callback(); });
    
    $dto = CrearPersonaDTO::fromArray([
        'tipo_persona' => 'N', 'sexo' => 'M', 'nombres' => 'Juan', 'direccion' => 'Dir 1', 'distrito' => 'Dist 1',
        'provincia' => 'Prov 1', 'departamento' => 'Dep 1', 'user_id' => 1, 'documentos' => [['tipo_documento_identidad_id' => 1, 'numero_documento' => '12345678']]
    ]);
    
    $personaFake = new Persona(1, TipoPersona::Natural, Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', \Carbon\CarbonImmutable::now(), \Carbon\CarbonImmutable::now(), 1);
    
    $repo->shouldReceive('crear')->with(Mockery::any())->once()->andReturn($personaFake);
    $docRepo->shouldReceive('crear')->with(Mockery::any())->once()->andReturn(new \App\Modules\Personas\Domain\Entities\PersonaDocumento(1, 1, 1, '123'));
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($personaFake);
    
    $useCase = new CrearPersonaUseCase($repo, $docRepo);
    $useCase->execute($dto);
});
