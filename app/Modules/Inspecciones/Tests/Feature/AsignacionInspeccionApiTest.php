<?php

declare(strict_types=1);

use App\Models\User;
use App\Shared\Domain\Calendario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

const URL_INSPECCIONES = '/api/lf-itse/inspecciones/';

beforeEach(function () {
    // Usuario con perfil; is_staff según sea personal de oficina o inspector de campo
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
    $this->carlos = ($this->usuario)(['es_inspector' => true], ['first_name' => 'CARLOS', 'last_name' => 'RUIZ', 'is_staff' => false]);
    $this->maria = ($this->usuario)(['es_inspector' => true], ['first_name' => 'MARIA', 'last_name' => 'PAZ', 'is_staff' => false]);

    $tupaItse = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['nombre' => 'ITSE RIESGO MEDIO', 'requiere_itse' => true, 'requiere_lf' => false]));
    $tupaLf = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['nombre' => 'SOLO LICENCIA', 'requiere_itse' => false, 'requiere_lf' => true]));
    $persona = DB::table('personas')->insertGetId(Registros::persona([
        'nombres' => 'LUIS', 'apellido_paterno' => 'TORRES', 'apellido_materno' => 'VELA', 'direccion' => 'JR. MARTINEZ 120', 'telefono' => '942000111',
    ]));
    $expediente = fn (int $numero, int $tupa, int $diasVence) => DB::table('expedientes')->insertGetId(Registros::expediente([
        'numero_expediente' => $numero, 'solicitante_id' => $persona, 'tipo_procedimiento_tupa_id' => $tupa,
        'fecha_vencimiento' => now()->addDays($diasVence)->toDateString(), 'fecha_alerta' => now()->addDays($diasVence - 2)->toDateString(),
    ]));
    $this->exp701 = $expediente(701, $tupaItse, 9);
    $this->exp702 = $expediente(702, $tupaItse, 4);
    $this->expSoloLf = $expediente(703, $tupaLf, 4);

    $this->asignar = fn (int $expedienteId, User $inspector, array $extra = []) => $this->actingAs($this->jefe, 'api')
        ->postJson(URL_INSPECCIONES.'asignaciones/', array_merge([
            'expediente_id' => $expedienteId, 'inspector_id' => $inspector->id,
            'nombre_comercial' => 'bodega el sol', 'direccion' => 'Jr. Martinez 120',
        ], $extra));
});

it('lista los expedientes que esperan inspección, con el local sugerido', function () {
    $lista = $this->actingAs($this->jefe, 'api')->getJson(URL_INSPECCIONES.'por-inspeccionar/')->assertOk()->json();

    // El de solo licencia no requiere ITSE; ordenados por vencimiento
    expect(array_column($lista, 'numero_expediente'))->toBe([702, 701])
        ->and($lista[0])->toMatchArray([
            'procedimiento' => 'ITSE RIESGO MEDIO',
            'solicitante' => 'TORRES VELA LUIS',
            'sugerido_direccion' => 'JR. MARTINEZ 120',
            'asignacion_id' => null,
        ]);
});

it('asigna un inspector y lo muestra en la bandeja del inspector', function () {
    $hoy = Calendario::hoy()->toDateString();
    ($this->asignar)($this->exp701, $this->carlos, ['fecha_programada' => $hoy, 'indicaciones' => '  Llevar luxómetro '])
        ->assertCreated()
        ->assertJson([
            'numero_expediente' => 701,
            'inspector_nombre' => 'CARLOS RUIZ',
            'nombre_comercial' => 'BODEGA EL SOL',
            'direccion' => 'JR. MARTINEZ 120',
            'fecha_programada' => $hoy,
            'indicaciones' => 'Llevar luxómetro',
            'asignado_por' => 'ANA JEFA',
            'vigente' => true,
        ]);

    $bandeja = $this->actingAs($this->carlos, 'api')->getJson(URL_INSPECCIONES.'mi-bandeja/')->assertOk()->json();
    expect($bandeja)->toHaveCount(1)
        ->and($bandeja[0])->toMatchArray(['numero_expediente' => 701, 'solicitante_telefono' => '942000111']);

    // María no ve inspecciones ajenas
    $this->actingAs($this->maria, 'api')->getJson(URL_INSPECCIONES.'mi-bandeja/')->assertOk()->assertExactJson([]);

    // Ya asignado: sale primero lo que falta asignar y se ve quién lo tiene
    $lista = $this->actingAs($this->jefe, 'api')->getJson(URL_INSPECCIONES.'por-inspeccionar/')->json();
    expect($lista[1])->toMatchArray(['numero_expediente' => 701, 'inspector_nombre' => 'CARLOS RUIZ', 'inspector_activo' => true]);

    $inspectores = collect($this->actingAs($this->jefe, 'api')->getJson(URL_INSPECCIONES.'inspectores/')->assertOk()->json());
    expect($inspectores->firstWhere('id', $this->carlos->id)['pendientes'])->toBe(1)
        ->and($inspectores->firstWhere('id', $this->maria->id)['pendientes'])->toBe(0);
});

it('no asigna dos veces, ni expedientes que no esperan inspección, ni a quien no es inspector', function () {
    ($this->asignar)($this->exp701, $this->carlos)->assertCreated();
    ($this->asignar)($this->exp701, $this->maria)
        ->assertStatus(409)
        ->assertJson(['error' => 'El expediente N° 701 ya tiene un inspector asignado. Use "Reasignar" para cambiarlo.']);

    ($this->asignar)($this->expSoloLf, $this->carlos)->assertStatus(422);

    DB::table('expedientes_anulaciones')->insert(['expediente_id' => $this->exp702, 'motivo' => 'Desistimiento del solicitante', 'usuario_id' => $this->jefe->id]);
    ($this->asignar)($this->exp702, $this->carlos)
        ->assertStatus(422)
        ->assertJsonFragment(['error' => 'El expediente N° 702 no espera inspección ITSE: no la requiere, ya tiene dictamen final, certificado o denegatoria, o está anulado.']);

    ($this->asignar)(999999, $this->carlos)->assertNotFound();
});

it('rechaza inspectores desactivados o sin la casilla, y fechas pasadas', function () {
    $oficina = ($this->usuario)(['itse' => true]);
    ($this->asignar)($this->exp701, $oficina)
        ->assertStatus(422)
        ->assertJson(['error' => 'El inspector no existe, está desactivado o no está marcado como inspector.']);

    DB::table('auth_user')->where('id', $this->maria->id)->update(['is_active' => false]);
    ($this->asignar)($this->exp701, $this->maria)->assertStatus(422);

    ($this->asignar)($this->exp701, $this->carlos, ['fecha_programada' => Calendario::hoy()->subDay()->toDateString()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['fecha_programada']);
    ($this->asignar)($this->exp701, $this->carlos, ['direccion' => '   '])
        ->assertUnprocessable();
});

it('reasigna dejando constancia de la asignación anterior', function () {
    $primera = ($this->asignar)($this->exp701, $this->carlos)->json('id');

    $this->actingAs($this->jefe, 'api')
        ->postJson(URL_INSPECCIONES."asignaciones/{$primera}/reasignar/", ['inspector_id' => $this->maria->id, 'motivo' => 'corto'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['motivo']);
    $this->actingAs($this->jefe, 'api')
        ->postJson(URL_INSPECCIONES."asignaciones/{$primera}/reasignar/", ['inspector_id' => $this->carlos->id, 'motivo' => 'Cambio de turno del inspector'])
        ->assertStatus(422);

    $nueva = $this->actingAs($this->jefe, 'api')
        ->postJson(URL_INSPECCIONES."asignaciones/{$primera}/reasignar/", ['inspector_id' => $this->maria->id, 'motivo' => 'Inspector con descanso médico'])
        ->assertCreated()
        ->assertJson(['inspector_nombre' => 'MARIA PAZ', 'nombre_comercial' => 'BODEGA EL SOL', 'vigente' => true])
        ->json('id');

    $this->actingAs($this->carlos, 'api')->getJson(URL_INSPECCIONES.'mi-bandeja/')->assertExactJson([]);
    expect($this->actingAs($this->maria, 'api')->getJson(URL_INSPECCIONES.'mi-bandeja/')->json())->toHaveCount(1);

    $historial = $this->actingAs($this->jefe, 'api')->getJson(URL_INSPECCIONES."expedientes/{$this->exp701}/historial/")->assertOk()->json();
    expect(array_column($historial, 'id'))->toBe([$nueva, $primera])
        ->and($historial[1])->toMatchArray([
            'vigente' => false, 'inspector_nombre' => 'CARLOS RUIZ',
            'reasignado_por' => 'ANA JEFA', 'motivo_reasignacion' => 'Inspector con descanso médico',
        ]);

    // La asignación cerrada ya no se reasigna ni se edita
    $this->actingAs($this->jefe, 'api')
        ->postJson(URL_INSPECCIONES."asignaciones/{$primera}/reasignar/", ['inspector_id' => $this->carlos->id, 'motivo' => 'Vuelve el inspector titular'])
        ->assertNotFound();
});

it('reprograma la visita y corrige el local sin cambiar de inspector', function () {
    $id = ($this->asignar)($this->exp701, $this->carlos)->json('id');
    $manana = Calendario::hoy()->addDay()->toDateString();

    $this->actingAs($this->jefe, 'api')
        ->putJson(URL_INSPECCIONES."asignaciones/{$id}/", [
            'nombre_comercial' => 'Bodega El Sol EIRL', 'direccion' => 'Jr. Martinez 122', 'fecha_programada' => $manana, 'indicaciones' => '',
        ])
        ->assertOk()
        ->assertJson(['id' => $id, 'nombre_comercial' => 'BODEGA EL SOL EIRL', 'direccion' => 'JR. MARTINEZ 122', 'fecha_programada' => $manana, 'indicaciones' => null]);
});

it('la bandeja deja de mostrar la inspección cuando ya se emitió el certificado', function () {
    ($this->asignar)($this->exp701, $this->carlos)->assertCreated();
    DB::table('autorizaciones_improcedentes')->insert(Registros::autorizacionImprocedente([
        'tipo_autorizacion' => 'ITSE', 'expediente_id' => $this->exp701, 'usuario_id' => $this->jefe->id,
    ]));

    $this->actingAs($this->carlos, 'api')->getJson(URL_INSPECCIONES.'mi-bandeja/')->assertExactJson([]);
    expect(array_column($this->actingAs($this->jefe, 'api')->getJson(URL_INSPECCIONES.'por-inspeccionar/')->json(), 'numero_expediente'))
        ->toBe([702]);
});

it('el inspector de campo solo entra a su bandeja', function () {
    $this->actingAs($this->carlos, 'api')->getJson('/api/auth/users/me/')->assertOk()->assertJsonPath('role', 'INSPECTOR');

    foreach (['por-inspeccionar/', 'inspectores/', 'expedientes/1/historial/'] as $ruta) {
        $this->actingAs($this->carlos, 'api')->getJson(URL_INSPECCIONES.$ruta)->assertForbidden();
    }
    // Las rutas generales del personal quedan fuera de su alcance
    $this->actingAs($this->carlos, 'api')->getJson('/api/lf-itse/personas/')->assertForbidden();
    $this->actingAs($this->carlos, 'api')->getJson('/api/lf-itse/itse/buscar/')->assertForbidden();
});

it('solo el jefe de ITSE asigna; ITSE sin jefatura no', function () {
    $this->getJson(URL_INSPECCIONES.'mi-bandeja/')->assertUnauthorized();

    $itse = ($this->usuario)(['itse' => true]);
    $this->actingAs($itse, 'api')->getJson(URL_INSPECCIONES.'por-inspeccionar/')->assertForbidden();
    $this->actingAs($itse, 'api')->postJson(URL_INSPECCIONES.'asignaciones/', [])->assertForbidden();
    $this->actingAs($itse, 'api')->getJson(URL_INSPECCIONES.'mi-bandeja/')->assertForbidden();
});

it('el personal de oficina marcado como inspector también tiene bandeja', function () {
    $mixto = ($this->usuario)(['itse' => true, 'es_inspector' => true], ['first_name' => 'PEDRO']);
    ($this->asignar)($this->exp702, $mixto)->assertCreated();

    $this->actingAs($mixto, 'api')->getJson('/api/auth/users/me/')->assertJsonPath('role', 'ADMIN_MUNICIPALIDAD');
    expect($this->actingAs($mixto, 'api')->getJson(URL_INSPECCIONES.'mi-bandeja/')->assertOk()->json())->toHaveCount(1);
});
