<?php

declare(strict_types=1);
use App\Modules\Personas\Application\Mappers\PersonaMapper;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\ValueObjects\Sexo;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use App\Modules\Personas\Infrastructure\Persistence\Repositories\EloquentPersonaRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentPersonaRepository(new PersonaMapper);
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

it('listarTodas devuelve items', function () {
    $persona = new Persona(null, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'C', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $this->repo->crear($persona);

    $res = $this->repo->listarTodas();
    expect(count($res))->toBe(1);
    expect($res[0]->nombres)->toBe('C');
});

it('eliminar borra el registro', function () {
    $persona = new Persona(null, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'C', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $creada = $this->repo->crear($persona);

    $this->repo->eliminar($creada->id);
    expect($this->repo->buscarPorId($creada->id))->toBeNull();
});

it('buscarPorFiltro con SQL', function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        test()->markTestSkipped('Requiere PG');
    }
    $persona = new Persona(null, TipoPersona::Natural, Sexo::Masculino, 'A', 'B', 'C', 'Dir', 'Dis', 'Pro', 'Dep', '123', 'a@a.com', CarbonImmutable::now(), CarbonImmutable::now(), 1);
    $this->repo->crear($persona);

    $res = $this->repo->buscarPorFiltro('NOMBRE', 'C');
    expect(count($res))->toBe(1);
    expect($res[0]['nombres'])->toBe('C');
});
