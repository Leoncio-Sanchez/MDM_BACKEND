import os

base = '/home/leo/Documentos/Muni/backend-php/app/Modules/Personas/Tests/Integration/Infrastructure'
os.makedirs(base, exist_ok=True)

def write(path, content):
    with open(f"{base}/{path}", "w") as f:
        f.write(content)

write('EloquentPersonaRepositoryTest.php', """<?php
use App\\Modules\\Personas\\Infrastructure\\Persistence\\Repositories\\EloquentPersonaRepository;
use App\\Modules\\Personas\\Infrastructure\\Persistence\\Models\\PersonaModel;
use App\\Modules\\Personas\\Application\\Mappers\\PersonaMapper;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona;
use App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Carbon\\CarbonImmutable;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentPersonaRepository(new PersonaMapper());
});

it('crea y busca una persona', function () {
    $persona = new Persona(null, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'C', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $creada = $this->repo->crear($persona);
    
    expect($creada->id)->not->toBeNull();
    
    $buscada = $this->repo->buscarPorId($creada->id);
    expect($buscada->nombres)->toBe('C');
});

it('actualiza una persona', function () {
    $persona = new Persona(null, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'C', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $creada = $this->repo->crear($persona);
    
    $actualizada = new Persona($creada->id, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'NUEVO', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $result = $this->repo->actualizar($actualizada);
    
    expect($result->nombres)->toBe('NUEVO');
});

it('paginar devuelve items y total', function () {
    $persona = new Persona(null, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'C', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $this->repo->crear($persona);
    
    $res = $this->repo->paginar(1, 10);
    expect($res['total'])->toBe(1);
    expect($res['items'][0]->nombres)->toBe('C');
});

it('eliminar borra el registro', function () {
    $persona = new Persona(null, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'C', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $creada = $this->repo->crear($persona);
    
    $this->repo->eliminar($creada->id);
    expect($this->repo->buscarPorId($creada->id))->toBeNull();
});

it('buscarPorFiltro con SQL', function () {
    $persona = new Persona(null, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'C', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $this->repo->crear($persona);
    
    $res = $this->repo->buscarPorFiltro('NOMBRE', 'C');
    expect(count($res))->toBe(1);
    expect($res[0]['nombres'])->toBe('C');
});
""")

write('ReniecHttpClientTest.php', """<?php
use App\\Modules\\Personas\\Infrastructure\\External\\ReniecHttpClient;
use App\\Modules\\Personas\\Domain\\Exceptions\\ReniecNoDisponible;
use App\\Modules\\Personas\\Domain\\Exceptions\\ReniecRespuestaInvalida;
use Illuminate\\Support\\Facades\\Http;

it('consulta correctamente y mapea DTO', function () {
    Http::fake([
        '*' => Http::response([
            'consultarResponse' => [
                'return' => [
                    'nuDni' => '12345678',
                    'prenombres' => 'Juan',
                    'apePaterno' => 'Perez',
                    'apeMaterno' => 'Gomez',
                ]
            ]
        ], 200)
    ]);
    
    $client = new ReniecHttpClient('url', 'dni', 'ruc', 'pass', 10);
    $res = $client->consultarPorDni('12345678');
    
    expect($res->dni)->toBe('12345678');
    expect($res->nombres)->toBe('Juan');
});

it('lanza excepcion si respuesta no es 200', function () {
    Http::fake([
        '*' => Http::response([], 500)
    ]);
    
    $client = new ReniecHttpClient('url', 'dni', 'ruc', 'pass', 10);
    $client->consultarPorDni('12345678');
})->throws(ReniecNoDisponible::class);

it('lanza excepcion si respuesta no tiene consultarResponse', function () {
    Http::fake([
        '*' => Http::response(['data' => 'invalid'], 200)
    ]);
    
    $client = new ReniecHttpClient('url', 'dni', 'ruc', 'pass', 10);
    $client->consultarPorDni('12345678');
})->throws(ReniecRespuestaInvalida::class);
""")

