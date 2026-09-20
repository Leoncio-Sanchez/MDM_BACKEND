<?php
use App\Modules\Personas\Infrastructure\Persistence\Repositories\EloquentPersonaRepository;
use App\Modules\Personas\Infrastructure\Persistence\Models\PersonaModel;
use App\Modules\Personas\Application\Mappers\PersonaMapper;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use App\Modules\Personas\Domain\ValueObjects\Sexo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\CarbonImmutable;

uses(\Tests\TestCase::class, RefreshDatabase::class);

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
    if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') { test()->markTestSkipped('Requiere PG'); }
    $persona = new Persona(null, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'C', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $this->repo->crear($persona);
    
    $res = $this->repo->buscarPorFiltro('NOMBRE', 'C');
    expect(count($res))->toBe(1);
    expect($res[0]['nombres'])->toBe('C');
});
