<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

const URL_EQUIPO = '/api/lf-itse/inspecciones/';

beforeEach(function () {
    Storage::fake('media');

    $this->usuario = function (array $perfil, array $datos = []): User {
        $id = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(array_merge(['username' => 'u_'.uniqid(), 'is_staff' => true], $datos)));
        DB::table('usuarios_perfiles')->insert(array_merge(
            ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false,
                'es_inspector' => false, 'es_jefe_itse' => false, 'reportes_completos' => false],
            $perfil,
            ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
        ));

        return User::find($id);
    };
    $this->jefe = ($this->usuario)(['itse' => true, 'es_jefe_itse' => true], ['first_name' => 'ANA', 'last_name' => 'JEFA']);
    $inspector = fn (string $nombre) => ($this->usuario)(['es_inspector' => true], ['first_name' => $nombre, 'is_staff' => false]);
    $this->carlos = $inspector('CARLOS');
    $this->maria = $inspector('MARIA');
    $this->jose = $inspector('JOSE');
    $this->rosa = $inspector('ROSA');
    $this->luis = $inspector('LUIS');

    $tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['requiere_itse' => true]));
    $persona = DB::table('personas')->insertGetId(Registros::persona());
    $this->expediente = DB::table('expedientes')->insertGetId(Registros::expediente([
        'numero_expediente' => 901, 'solicitante_id' => $persona, 'tipo_procedimiento_tupa_id' => $tupa,
    ]));

    $this->asignar = fn (array $extra = []) => $this->actingAs($this->jefe, 'api')
        ->postJson(URL_EQUIPO.'asignaciones/', array_merge([
            'expediente_id' => $this->expediente, 'inspector_id' => $this->carlos->id,
            'nombre_comercial' => 'BODEGA', 'direccion' => 'JR. LIMA 1',
        ], $extra));
    $this->editar = fn (int $id, array $extra = []) => $this->actingAs($this->jefe, 'api')
        ->putJson(URL_EQUIPO."asignaciones/{$id}/", array_merge(['nombre_comercial' => 'BODEGA', 'direccion' => 'JR. LIMA 1'], $extra));
    $this->bandeja = fn (User $quien) => $this->actingAs($quien, 'api')->getJson(URL_EQUIPO.'mi-bandeja/')->assertOk()->json();
    $this->registrarActa = fn (User $quien, int $asignacion) => $this->actingAs($quien, 'api')
        ->post(URL_EQUIPO."mi-bandeja/{$asignacion}/acta/", [
            'numero_acta' => 'ACTA-901', 'fecha_visita' => now()->subHour()->toIso8601String(),
            'area_m2' => '40', 'aforo' => 20,
            'extintores' => 'CUMPLE', 'senalizacion' => 'CUMPLE', 'luces_emergencia' => 'CUMPLE', 'tableros_electricos' => 'CUMPLE',
            'fotos' => [
                ['tipo' => 'FACHADA', 'archivo' => UploadedFile::fake()->image('fachada.jpg')],
                ['tipo' => 'EXTINTORES', 'archivo' => UploadedFile::fake()->image('extintor.jpg')],
                ['tipo' => 'TABLEROS', 'archivo' => UploadedFile::fake()->image('tablero.jpg')],
            ],
        ], ['Accept' => 'application/json']);
});

it('asigna un responsable con acompañantes: todos la ven en su bandeja', function () {
    ($this->asignar)(['acompanantes' => [$this->maria->id, $this->jose->id]])
        ->assertCreated()
        ->assertJsonPath('inspector_nombre', 'CARLOS')
        ->assertJsonPath('acompanantes', [
            ['id' => $this->maria->id, 'nombre' => 'MARIA', 'activo' => true],
            ['id' => $this->jose->id, 'nombre' => 'JOSE', 'activo' => true],
        ]);

    expect(($this->bandeja)($this->carlos)[0])->toMatchArray(['numero_expediente' => 901, 'es_responsable' => true])
        ->and(($this->bandeja)($this->maria)[0])->toMatchArray(['numero_expediente' => 901, 'es_responsable' => false, 'inspector_nombre' => 'CARLOS'])
        ->and(($this->bandeja)($this->rosa))->toBe([]);

    $lista = $this->actingAs($this->jefe, 'api')->getJson(URL_EQUIPO.'por-inspeccionar/')->json();
    expect(array_column($lista[0]['acompanantes'], 'nombre'))->toBe(['MARIA', 'JOSE']);

    $inspectores = collect($this->actingAs($this->jefe, 'api')->getJson(URL_EQUIPO.'inspectores/')->json());
    expect($inspectores->firstWhere('id', $this->carlos->id))->toMatchArray(['pendientes' => 1, 'acompanando' => 0])
        ->and($inspectores->firstWhere('id', $this->maria->id))->toMatchArray(['pendientes' => 0, 'acompanando' => 1]);
});

it('valida el equipo: sin repetir al responsable, máximo 3 y solo inspectores activos', function () {
    ($this->asignar)(['acompanantes' => [$this->carlos->id]])
        ->assertStatus(422)
        ->assertJson(['error' => 'El inspector responsable no puede figurar también como acompañante.']);
    ($this->asignar)(['acompanantes' => [$this->maria->id, $this->jose->id, $this->rosa->id, $this->luis->id]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['acompanantes']);
    ($this->asignar)(['acompanantes' => [$this->maria->id, $this->maria->id]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['acompanantes.0']);

    DB::table('auth_user')->where('id', $this->rosa->id)->update(['is_active' => false]);
    ($this->asignar)(['acompanantes' => [$this->rosa->id]])
        ->assertStatus(422)
        ->assertJson(['error' => 'Uno de los acompañantes no existe, está desactivado o no está marcado como inspector.']);

    expect(DB::table('inspecciones_asignaciones')->count())->toBe(0);
});

it('cambia los acompañantes sin cambiar de responsable; quien sale queda con constancia', function () {
    $id = ($this->asignar)(['acompanantes' => [$this->maria->id]])->json('id');

    ($this->editar)($id, ['acompanantes' => [$this->jose->id]])
        ->assertOk()
        ->assertJsonPath('acompanantes.0.nombre', 'JOSE')
        ->assertJsonCount(1, 'acompanantes');

    expect(($this->bandeja)($this->maria))->toBe([])
        ->and(($this->bandeja)($this->jose))->toHaveCount(1);
    $retiro = DB::table('inspecciones_asignaciones_acompanantes')->where('inspector_id', $this->maria->id)->first();
    expect($retiro->fecha_retiro)->not->toBeNull()
        ->and((int) $retiro->retirado_por)->toBe($this->jefe->id);

    // Sin el campo, el equipo queda como está; con [] se queda solo el responsable
    ($this->editar)($id, ['fecha_programada' => now()->addDay()->toDateString()])->assertOk()->assertJsonCount(1, 'acompanantes');
    ($this->editar)($id, ['acompanantes' => []])->assertOk()->assertJsonCount(0, 'acompanantes');
    expect(DB::table('inspecciones_asignaciones_acompanantes')->count())->toBe(2);
});

it('al reasignar a un acompañante como responsable, deja de figurar como acompañante', function () {
    $primera = ($this->asignar)(['acompanantes' => [$this->maria->id, $this->jose->id]])->json('id');

    $nueva = $this->actingAs($this->jefe, 'api')
        ->postJson(URL_EQUIPO."asignaciones/{$primera}/reasignar/", ['inspector_id' => $this->maria->id, 'motivo' => 'Inspector con descanso médico'])
        ->assertCreated()
        ->assertJsonPath('inspector_nombre', 'MARIA')
        ->assertJsonPath('acompanantes', [['id' => $this->jose->id, 'nombre' => 'JOSE', 'activo' => true]])
        ->json('id');

    // La asignación cerrada conserva su equipo como constancia
    $historial = collect($this->actingAs($this->jefe, 'api')->getJson(URL_EQUIPO."expedientes/{$this->expediente}/historial/")->json());
    expect(array_column($historial->firstWhere('id', $primera)['acompanantes'], 'nombre'))->toBe(['MARIA', 'JOSE'])
        ->and(($this->bandeja)($this->carlos))->toBe([])
        ->and(($this->bandeja)($this->jose)[0]['id'])->toBe($nueva);

    // También se puede indicar otro equipo al reasignar
    $this->actingAs($this->jefe, 'api')
        ->postJson(URL_EQUIPO."asignaciones/{$nueva}/reasignar/", [
            'inspector_id' => $this->rosa->id, 'acompanantes' => [$this->luis->id], 'motivo' => 'Reorganización de turnos',
        ])
        ->assertCreated()
        ->assertJsonPath('acompanantes.0.nombre', 'LUIS')
        ->assertJsonCount(1, 'acompanantes');
});

it('el acta la registra el responsable y guarda quiénes lo acompañaron', function () {
    $id = ($this->asignar)(['acompanantes' => [$this->maria->id, $this->jose->id]])->json('id');

    ($this->registrarActa)($this->maria, $id)
        ->assertStatus(422)
        ->assertJson(['error' => 'Usted acompaña esta inspección: el acta la registra el inspector responsable (CARLOS).']);
    ($this->registrarActa)($this->rosa, $id)->assertNotFound();

    // José no fue a la visita: el jefe lo saca del equipo antes de que se registre el acta
    ($this->editar)($id, ['acompanantes' => [$this->maria->id]])->assertOk();

    $acta = ($this->registrarActa)($this->carlos, $id)
        ->assertCreated()
        ->assertJsonPath('acompanantes', [['id' => $this->maria->id, 'nombre' => 'MARIA']])
        ->json();

    // María ve el acta que acompañó; José y Rosa no
    $this->actingAs($this->maria, 'api')->getJson(URL_EQUIPO."mi-bandeja/actas/{$acta['id']}/")->assertOk();
    $this->actingAs($this->jose, 'api')->getJson(URL_EQUIPO."mi-bandeja/actas/{$acta['id']}/")->assertNotFound();
    $this->actingAs($this->rosa, 'api')->getJson(URL_EQUIPO."mi-bandeja/actas/{$acta['id']}/")->assertNotFound();

    $this->actingAs($this->jefe, 'api')->getJson(URL_EQUIPO."expedientes/{$this->expediente}/actas/")
        ->assertOk()
        ->assertJsonPath('0.acompanantes.0.nombre', 'MARIA');
});
