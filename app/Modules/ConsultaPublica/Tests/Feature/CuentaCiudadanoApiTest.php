<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

const URL_CUENTA = '/api/publico/cuenta/';

beforeEach(function () {
    Cache::flush(); // los límites de intentos viven en la caché

    $this->persona = function (string $documento): int {
        $id = DB::table('personas')->insertGetId(Registros::persona(['nombres' => 'TITULAR '.$documento]));
        DB::table('personas_documentos')->insert(['numero_documento' => $documento, 'persona_id' => $id, 'tipo_documento_identidad_id' => 1]);

        return $id;
    };
    $this->tupaItse = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa([
        'nombre' => 'LICENCIA CON ITSE', 'requiere_itse' => true, 'requiere_lf' => true,
    ]));
    $this->expediente = fn (int $numero, int $solicitante, array $extra = []) => DB::table('expedientes')->insertGetId(Registros::expediente(array_merge([
        'numero_expediente' => $numero, 'solicitante_id' => $solicitante, 'tipo_procedimiento_tupa_id' => $this->tupaItse,
    ], $extra)));

    $this->luis = ($this->persona)('12345678');
    $this->ana = ($this->persona)('87654321');
    $this->expLuis = ($this->expediente)(1250, $this->luis);
    ($this->expediente)(1251, $this->ana);

    $this->cuenta = fn (string $accion, array $datos) => $this->postJson(URL_CUENTA.$accion.'/', $datos);
    $this->datos = fn (array $cambios = []) => array_merge([
        'documento' => '12345678', 'numero_expediente' => 1250, 'clave' => 'Bodega2026', 'clave_confirmation' => 'Bodega2026',
    ], $cambios);
});

it('el ciudadano crea su cuenta con un expediente suyo y queda con la sesión iniciada', function () {
    $tokens = ($this->cuenta)('registro', ($this->datos)())->assertCreated()->assertJsonStructure(['access', 'refresh'])->json();

    $this->withToken($tokens['access'])->getJson('/api/auth/users/me/')
        ->assertOk()
        ->assertJsonPath('role', 'CIUDADANO');
    expect(DB::table('auth_user')->where('username', 'ciu_12345678')->value('is_staff'))->toBeFalse();
});

it('no revela si el documento existe: mismo mensaje si no hay persona o si el expediente es ajeno', function () {
    $ajeno = ($this->cuenta)('registro', ($this->datos)(['numero_expediente' => 1251]))->assertUnprocessable()->json();
    $inexistente = ($this->cuenta)('registro', ($this->datos)(['documento' => '11112222']))->assertUnprocessable()->json();

    expect($ajeno)->toBe($inexistente)
        ->and($ajeno['error'])->toContain('no corresponde a ese DNI o RUC')
        ->and(DB::table('ciudadanos_cuentas')->count())->toBe(0);
});

it('el representante también puede crear su cuenta', function () {
    $representante = ($this->persona)('44556677');
    ($this->expediente)(1300, $this->ana, ['representante_id' => $representante]);

    ($this->cuenta)('registro', ($this->datos)(['documento' => '44556677', 'numero_expediente' => 1300]))->assertCreated();
});

it('valida la contraseña y no crea dos cuentas para el mismo documento', function () {
    ($this->cuenta)('registro', ($this->datos)(['clave' => 'corta1', 'clave_confirmation' => 'corta1']))->assertUnprocessable()->assertJsonValidationErrors('clave');
    ($this->cuenta)('registro', ($this->datos)(['clave' => 'solotexto', 'clave_confirmation' => 'solotexto']))->assertUnprocessable()->assertJsonValidationErrors('clave');
    ($this->cuenta)('registro', ($this->datos)(['clave_confirmation' => 'Otra2026x']))->assertUnprocessable()->assertJsonValidationErrors('clave');
    ($this->cuenta)('registro', ($this->datos)(['clave' => '12345678a', 'clave_confirmation' => '12345678a']))->assertCreated();

    Cache::flush(); // los límites de intentos viven en la caché
    ($this->cuenta)('registro', ($this->datos)())
        ->assertUnprocessable()
        ->assertJsonPath('error', 'Ese DNI o RUC ya tiene una cuenta. Ingrese con su contraseña o, si la olvidó, use «Olvidé mi contraseña».');
});

it('la contraseña no puede ser el propio documento', function () {
    $persona = ($this->persona)('20123456789');
    ($this->expediente)(1400, $persona);

    ($this->cuenta)('registro', ($this->datos)(['documento' => '20123456789', 'numero_expediente' => 1400, 'clave' => '20123456789', 'clave_confirmation' => '20123456789']))
        ->assertUnprocessable();
});

it('ingresa con su DNI y contraseña', function () {
    ($this->cuenta)('registro', ($this->datos)())->assertCreated();

    ($this->cuenta)('ingresar', ['documento' => '12345678', 'clave' => 'Bodega2026'])->assertOk()->assertJsonStructure(['access', 'refresh']);
    ($this->cuenta)('ingresar', ['documento' => '12345678', 'clave' => 'Equivocada1'])
        ->assertUnauthorized()
        ->assertJsonPath('detail', 'DNI/RUC o contraseña incorrectos.');
});

it('restablece la contraseña con la misma prueba del registro', function () {
    ($this->cuenta)('restablecer', ($this->datos)())->assertUnprocessable()->assertJsonPath('error', 'Ese DNI o RUC todavía no tiene una cuenta. Créela con «Crear cuenta».');
    ($this->cuenta)('registro', ($this->datos)())->assertCreated();

    ($this->cuenta)('restablecer', ($this->datos)(['numero_expediente' => 1251, 'clave' => 'Nueva2027', 'clave_confirmation' => 'Nueva2027']))->assertUnprocessable();
    ($this->cuenta)('restablecer', ($this->datos)(['clave' => 'Nueva2027', 'clave_confirmation' => 'Nueva2027']))->assertOk()->assertJsonStructure(['access']);

    Cache::flush(); // los límites de intentos viven en la caché
    ($this->cuenta)('ingresar', ['documento' => '12345678', 'clave' => 'Bodega2026'])->assertUnauthorized();
    ($this->cuenta)('ingresar', ['documento' => '12345678', 'clave' => 'Nueva2027'])->assertOk();
    expect(DB::table('ciudadanos_cuentas')->where('documento', '12345678')->value('fecha_restablecimiento'))->not->toBeNull();
});

it('limita los intentos para que no se prueben expedientes en masa', function () {
    foreach (range(1, 5) as $intento) {
        ($this->cuenta)('registro', ($this->datos)(['numero_expediente' => 9000 + $intento]))->assertUnprocessable();
    }
    ($this->cuenta)('registro', ($this->datos)())->assertTooManyRequests();
});

it('ve solo sus trámites, con el avance de la inspección', function () {
    $tokens = ($this->cuenta)('registro', ($this->datos)())->json();
    ($this->expediente)(1260, $this->luis);
    DB::table('licencias_funcionamiento')->insert(Registros::licencia(['numero_licencia' => 501, 'nombre_comercial' => 'BODEGA LUCHITO', 'titular_id' => $this->luis]));
    DB::table('licencias_funcionamiento')->insert(Registros::licencia(['numero_licencia' => 502, 'nombre_comercial' => 'AJENA', 'titular_id' => $this->ana]));

    // Inspección del expediente 1250: asignada con fecha y luego observada
    $jefe = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(['username' => 'jefe', 'is_staff' => true]));
    $asignacion = DB::table('inspecciones_asignaciones')->insertGetId([
        'expediente_id' => $this->expLuis, 'inspector_id' => $jefe, 'nombre_comercial' => 'BODEGA', 'direccion' => 'JR. LIMA 1',
        'fecha_programada' => '2026-11-05', 'asignado_por' => $jefe,
    ]);

    $tramites = fn () => $this->withToken($tokens['access'])->getJson('/api/ciudadano/mis-tramites/')->assertOk()->json();

    $antes = $tramites();
    expect($antes['documento'])->toBe('12345678')
        ->and(array_column($antes['expedientes'], 'numero'))->toEqualCanonicalizing([1250, 1260])
        ->and(array_column($antes['licencias'], 'nombre_comercial'))->toBe(['BODEGA LUCHITO'])
        ->and(collect($antes['expedientes'])->firstWhere('numero', 1250)['inspeccion'])->toBe(['estado' => 'PROGRAMADA', 'fecha' => '2026-11-05'])
        ->and(collect($antes['expedientes'])->firstWhere('numero', 1260)['inspeccion'])->toBe(['estado' => 'POR_ASIGNAR', 'fecha' => null]);

    DB::table('inspecciones_actas')->insert([
        'expediente_id' => $this->expLuis, 'asignacion_id' => $asignacion, 'inspector_id' => $jefe, 'numero_acta' => 'A-1', 'visita' => 1,
        'fecha_visita' => now(), 'area_m2' => 40, 'aforo' => 20, 'extintores' => 'NO_CUMPLE', 'senalizacion' => 'CUMPLE',
        'luces_emergencia' => 'CUMPLE', 'tableros_electricos' => 'CUMPLE', 'resultado' => 'OBSERVADO',
        'fecha_limite_subsanacion' => '2026-11-03', 'fecha_registro' => now(),
    ]);
    expect(collect($tramites()['expedientes'])->firstWhere('numero', 1250)['inspeccion'])->toBe(['estado' => 'OBSERVADA', 'fecha' => '2026-11-03']);

    // Nada de datos personales ni del acta
    $contenido = $this->withToken($tokens['access'])->getJson('/api/ciudadano/mis-tramites/')->getContent();
    expect($contenido)->not->toContain('TITULAR')->not->toContain('JR. LIMA')->not->toContain('A-1');
});

it('el personal no usa "Mis trámites" y el ciudadano no entra al panel', function () {
    $tokens = ($this->cuenta)('registro', ($this->datos)())->json();
    $personal = User::find(DB::table('auth_user')->insertGetId(UsuarioAuth::datos(['username' => 'mesa', 'is_staff' => true])));

    $this->actingAs($personal, 'api')->getJson('/api/ciudadano/mis-tramites/')->assertForbidden();
    $this->withToken($tokens['access'])->getJson('/api/lf-itse/expedientes/')->assertForbidden();
    $this->withToken($tokens['access'])->getJson('/api/lf-itse/personas/')->assertForbidden();
});
