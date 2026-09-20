import os

base = '/home/leo/Documentos/Muni/backend-php/app/Modules/Personas/Tests/Unit/Application'
os.makedirs(base, exist_ok=True)

def write(path, content):
    with open(f"{base}/{path}", "w") as f:
        f.write(content)

write('ListarPersonasUseCaseTest.php', """<?php
use App\\Modules\\Personas\\Application\\UseCases\\ListarPersonasUseCase;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;

it('lista personas con paginacion por defecto', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('paginar')->with(1, 10)->once()->andReturn(['items' => [], 'total' => 0]);
    $useCase = new ListarPersonasUseCase($repo);
    $result = $useCase->execute();
    expect($result)->toBe(['items' => [], 'total' => 0]);
});

it('lista personas con paginacion custom', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('paginar')->with(2, 20)->once()->andReturn(['items' => [], 'total' => 0]);
    $useCase = new ListarPersonasUseCase($repo);
    $result = $useCase->execute(2, 20);
    expect($result)->toBe(['items' => [], 'total' => 0]);
});
""")

write('CrearPersonaUseCaseTest.php', """<?php
use App\\Modules\\Personas\\Application\\UseCases\\CrearPersonaUseCase;
use App\\Modules\\Personas\\Application\\DTOs\\CrearPersonaDTO;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona;
use App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo;
use Illuminate\\Support\\Facades\\DB;

it('crea una persona correctamente', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) { return $callback(); });
    
    $dto = new CrearPersonaDTO(TipoPersona::Natural, Sexo::Masculino, 'Juan', 'Dir 1', 'Dist 1', 'Prov 1', 'Dep 1', 1, 'Perez', 'Gomez', null, null, []);
    $personaFake = Mockery::mock(Persona::class);
    $personaFake->id = 1;
    $repo->shouldReceive('crear')->once()->andReturn($personaFake);
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
    
    $personaFake = Mockery::mock(Persona::class);
    $personaFake->id = 1;
    $repo->shouldReceive('crear')->once()->andReturn($personaFake);
    $docRepo->shouldReceive('crear')->once();
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($personaFake);
    
    $useCase = new CrearPersonaUseCase($repo, $docRepo);
    $useCase->execute($dto);
});
""")

write('ObtenerPersonaUseCaseTest.php', """<?php
use App\\Modules\\Personas\\Application\\UseCases\\ObtenerPersonaUseCase;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use App\\Modules\\Personas\\Domain\\Exceptions\\PersonaNoEncontrada;

it('obtiene una persona correctamente', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $persona = Mockery::mock(Persona::class);
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
""")

write('ActualizarPersonaUseCaseTest.php', """<?php
use App\\Modules\\Personas\\Application\\UseCases\\ActualizarPersonaUseCase;
use App\\Modules\\Personas\\Application\\DTOs\\ActualizarPersonaDTO;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use Illuminate\\Support\\Facades\\DB;

it('actualiza persona correctamente sin tocar documentos', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) { return $callback(); });
    
    $dto = new ActualizarPersonaDTO(null, null, 'Pedro', null, null, null, null, null, null, null, null, null);
    $persona = Mockery::mock(Persona::class);
    $personaActualizada = Mockery::mock(Persona::class);
    
    $repo->shouldReceive('buscarPorId')->with(1)->times(2)->andReturn($persona);
    $persona->shouldReceive('actualizar')->once()->andReturn($personaActualizada);
    $repo->shouldReceive('actualizar')->with($personaActualizada)->once();
    
    $useCase = new ActualizarPersonaUseCase($repo, $docRepo);
    $useCase->execute(1, $dto);
});

it('actualiza persona y reemplaza documentos', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) { return $callback(); });
    
    $dto = ActualizarPersonaDTO::fromArray(['nombres' => 'Pedro', 'documentos' => []]);
    $persona = Mockery::mock(Persona::class);
    $personaActualizada = Mockery::mock(Persona::class);
    
    $repo->shouldReceive('buscarPorId')->with(1)->times(2)->andReturn($persona);
    $persona->shouldReceive('actualizar')->once()->andReturn($personaActualizada);
    $repo->shouldReceive('actualizar')->with($personaActualizada)->once();
    $docRepo->shouldReceive('eliminarPorPersona')->with(1)->once();
    
    $useCase = new ActualizarPersonaUseCase($repo, $docRepo);
    $useCase->execute(1, $dto);
});
""")

write('EliminarPersonaUseCaseTest.php', """<?php
use App\\Modules\\Personas\\Application\\UseCases\\EliminarPersonaUseCase;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use App\\Modules\\Personas\\Domain\\Exceptions\\PersonaTieneDependientes;
use Illuminate\\Support\\Facades\\DB;

it('elimina persona correctamente', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) { return $callback(); });
    
    $persona = Mockery::mock(Persona::class);
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
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) { return $callback(); });
    
    $persona = Mockery::mock(Persona::class);
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($persona);
    $repo->shouldReceive('tieneDependientes')->with(1)->once()->andReturn(true);
    
    $useCase = new EliminarPersonaUseCase($repo, $docRepo);
    $useCase->execute(1);
})->throws(PersonaTieneDependientes::class);
""")

write('BuscarPersonasUseCaseTest.php', """<?php
use App\\Modules\\Personas\\Application\\UseCases\\BuscarPersonasUseCase;
use App\\Modules\\Personas\\Application\\DTOs\\BuscarPersonasDTO;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;

it('busca por filtro NOMBRE', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorFiltro')->with('NOMBRE', 'juan')->once()->andReturn([['id' => 1]]);
    $useCase = new BuscarPersonasUseCase($repo);
    $result = $useCase->execute(new BuscarPersonasDTO('NOMBRE', 'juan'));
    expect($result)->toBe([['id' => 1]]);
});

it('busca por filtro DOCUMENTO', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorFiltro')->with('DOCUMENTO', '1234')->once()->andReturn([['id' => 2]]);
    $useCase = new BuscarPersonasUseCase($repo);
    $result = $useCase->execute(new BuscarPersonasDTO('DOCUMENTO', '1234'));
    expect($result)->toBe([['id' => 2]]);
});

it('retorna vacío si no hay resultados', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $repo->shouldReceive('buscarPorFiltro')->with('ID', '99')->once()->andReturn([]);
    $useCase = new BuscarPersonasUseCase($repo);
    $result = $useCase->execute(new BuscarPersonasDTO('ID', '99'));
    expect($result)->toBe([]);
});
""")

write('ListarSexosUseCaseTest.php', """<?php
use App\\Modules\\Personas\\Application\\UseCases\\ListarSexosUseCase;

it('lista los sexos disponibles', function () {
    $useCase = new ListarSexosUseCase();
    $result = $useCase->execute();
    expect($result)->toHaveCount(3);
    expect($result[0]['value'])->toBe('M');
});

it('lista con label prefiero no decirlo', function () {
    $useCase = new ListarSexosUseCase();
    $result = $useCase->execute();
    expect($result[2]['label'])->toBe('Prefiero no decirlo');
});
""")

write('ListarDocumentosPersonaUseCaseTest.php', """<?php
use App\\Modules\\Personas\\Application\\UseCases\\ListarDocumentosPersonaUseCase;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use App\\Modules\\Personas\\Domain\\Exceptions\\PersonaNoEncontrada;

it('lista documentos de una persona', function () {
    $repo = Mockery::mock(PersonaRepositoryInterface::class);
    $docRepo = Mockery::mock(PersonaDocumentoRepositoryInterface::class);
    $persona = Mockery::mock(Persona::class);
    
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
""")

write('ConsultarReniecUseCaseTest.php', """<?php
use App\\Modules\\Personas\\Application\\UseCases\\ConsultarReniecUseCase;
use App\\Modules\\Personas\\Application\\DTOs\\ConsultarReniecDTO;
use App\\Modules\\Personas\\Application\\DTOs\\PersonaReniecDTO;
use App\\Modules\\Personas\\Domain\\Contracts\\ReniecClientInterface;
use App\\Modules\\Personas\\Domain\\Exceptions\\DocumentoInvalido;
use App\\Modules\\Personas\\Domain\\Exceptions\\ReniecNoDisponible;
use Psr\\Log\\LoggerInterface;

it('consulta RENIEC con DNI válido', function () {
    $reniec = Mockery::mock(ReniecClientInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);
    $dto = new PersonaReniecDTO('12345678', 'Juan', 'Perez', 'Gomez', null, null);
    
    $logger->shouldReceive('info')->once();
    $reniec->shouldReceive('consultarPorDni')->with('12345678')->once()->andReturn($dto);
    
    $useCase = new ConsultarReniecUseCase($reniec, $logger);
    expect($useCase->execute(new ConsultarReniecDTO('12345678')))->toBe($dto);
});

it('lanza DocumentoInvalido con DNI inválido', function () {
    $reniec = Mockery::mock(ReniecClientInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);
    
    $useCase = new ConsultarReniecUseCase($reniec, $logger);
    $useCase->execute(new ConsultarReniecDTO('123A'));
})->throws(DocumentoInvalido::class);

it('propaga ReniecNoDisponibleError si PIDE falla', function () {
    $reniec = Mockery::mock(ReniecClientInterface::class);
    $logger = Mockery::mock(LoggerInterface::class);
    
    $logger->shouldReceive('info')->once();
    $logger->shouldReceive('error')->once();
    $reniec->shouldReceive('consultarPorDni')->with('12345678')->once()->andThrow(ReniecNoDisponible::porDni('12345678'));
    
    $useCase = new ConsultarReniecUseCase($reniec, $logger);
    $useCase->execute(new ConsultarReniecDTO('12345678'));
})->throws(ReniecNoDisponible::class);
""")

