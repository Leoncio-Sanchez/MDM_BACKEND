<?php

declare(strict_types=1);
use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use App\Modules\Personas\Infrastructure\Persistence\Repositories\EloquentPersonaDocumentoRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentPersonaDocumentoRepository;
});

it('crea y lista documentos por persona', function () {
    // Para que SQLite no falle con foreign keys, insertamos la persona y tipo_documento directamente o usamos Model::insert
    $personaId = DB::table('personas')->insertGetId(\Tests\Fixtures\Registros::persona([
        'tipo_persona' => 'N',
        'sexo' => 'M',
        'nombres' => 'A',
        'direccion' => 'D',
        'distrito' => 'D',
        'provincia' => 'P',
        'departamento' => 'D',
        'fecha_creacion' => now(),
        'fecha_actualizacion' => now(),
        'user_id' => 1,
    ]));

    $tipoId = DB::table('tipos_documento_identidad')->insertGetId(\Tests\Fixtures\Registros::tipoDocumentoIdentidad([
        'codigo' => 'DNI', 'nombre' => 'DNI',
    ]));

    $doc = new PersonaDocumento(null, $personaId, $tipoId, '12345678');
    $creado = $this->repo->crear($doc);

    expect($creado->id)->not->toBeNull();

    $lista = $this->repo->listarPorPersona($personaId);
    expect(count($lista))->toBe(1);
    expect($lista[0]->numeroDocumento)->toBe('12345678');
});

it('elimina documentos por persona', function () {
    $personaId = DB::table('personas')->insertGetId(\Tests\Fixtures\Registros::persona([
        'tipo_persona' => 'N', 'sexo' => 'M', 'nombres' => 'A', 'direccion' => 'D', 'distrito' => 'D', 'provincia' => 'P', 'departamento' => 'D', 'fecha_creacion' => now(), 'fecha_actualizacion' => now(), 'user_id' => 1,
    ]));
    $tipoId = DB::table('tipos_documento_identidad')->insertGetId(\Tests\Fixtures\Registros::tipoDocumentoIdentidad([
        'codigo' => 'DNI', 'nombre' => 'DNI',
    ]));

    $this->repo->crear(new PersonaDocumento(null, $personaId, $tipoId, '12345678'));

    $this->repo->eliminarPorPersona($personaId);
    expect(count($this->repo->listarPorPersona($personaId)))->toBe(0);
});
