<?php
use App\Modules\Personas\Infrastructure\Persistence\Repositories\EloquentPersonaDocumentoRepository;
use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use App\Modules\Personas\Infrastructure\Persistence\Models\PersonaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\CarbonImmutable;

uses(\Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    if (!\Illuminate\Support\Facades\Schema::hasTable('tipos_documento_identidad')) {
        \Illuminate\Support\Facades\Schema::create('tipos_documento_identidad', function ($table) {
            $table->id();
            $table->string('codigo');
            $table->string('nombre');
            $table->integer('numero_digitos');
            $table->string('tipo_persona');
        });
    }

    $this->repo = new EloquentPersonaDocumentoRepository();
});

it('crea y lista documentos por persona', function () {
    // Para que SQLite no falle con foreign keys, insertamos la persona y tipo_documento directamente o usamos Model::insert
    $personaId = \Illuminate\Support\Facades\DB::table('personas')->insertGetId([
        'tipo_persona' => 'N',
        'sexo' => 'M',
        'nombres' => 'A',
        'direccion' => 'D',
        'distrito' => 'D',
        'provincia' => 'P',
        'departamento' => 'D',
        'fecha_creacion' => now(),
        'fecha_actualizacion' => now(),
        'user_id' => 1
    ]);
    
    $tipoId = \Illuminate\Support\Facades\DB::table('tipos_documento_identidad')->insertGetId([
        'codigo' => 'DNI', 'nombre' => 'DNI', 'numero_digitos' => 8, 'tipo_persona' => 'N'
    ]);
    
    $doc = new PersonaDocumento(null, $personaId, $tipoId, '12345678');
    $creado = $this->repo->crear($doc);
    
    expect($creado->id)->not->toBeNull();
    
    $lista = $this->repo->listarPorPersona($personaId);
    expect(count($lista))->toBe(1);
    expect($lista[0]->numeroDocumento)->toBe('12345678');
});

it('elimina documentos por persona', function () {
    $personaId = \Illuminate\Support\Facades\DB::table('personas')->insertGetId([
        'tipo_persona' => 'N', 'sexo' => 'M', 'nombres' => 'A', 'direccion' => 'D', 'distrito' => 'D', 'provincia' => 'P', 'departamento' => 'D', 'fecha_creacion' => now(), 'fecha_actualizacion' => now(), 'user_id' => 1
    ]);
    $tipoId = \Illuminate\Support\Facades\DB::table('tipos_documento_identidad')->insertGetId([
        'codigo' => 'DNI', 'nombre' => 'DNI', 'numero_digitos' => 8, 'tipo_persona' => 'N'
    ]);
    
    $this->repo->crear(new PersonaDocumento(null, $personaId, $tipoId, '12345678'));
    
    $this->repo->eliminarPorPersona($personaId);
    expect(count($this->repo->listarPorPersona($personaId)))->toBe(0);
});

