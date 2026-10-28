<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;

uses(Tests\TestCase::class, RefreshDatabase::class);

const URL_RESUMEN = '/api/lf-itse/dashboard/resumen/';

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-10-28 10:00:00');
    \Illuminate\Support\Carbon::setTestNow('2026-10-28 10:00:00');

    /** Personal municipal (staff) sin ningún acceso del perfil */
    $this->personal = function (): User {
        $id = DB::table('auth_user')->insertGetId(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'personal_'.uniqid(), 'is_staff' => true,
        ]));

        /** @var User $usuario */
        $usuario = User::find($id);

        return $usuario;
    };

    $this->estadoInactivo = DB::table('estados')->insertGetId(Registros::estado(['nombre' => 'CLAUSURA', 'esta_activo' => false]));
});

afterEach(function () {
    CarbonImmutable::setTestNow();
    \Illuminate\Support\Carbon::setTestNow();
});

it('cuenta los expedientes en trámite, por vencer y vencidos', function () {
    $tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['requiere_lf' => true]));
    $expediente = fn (string $alerta, string $vencimiento) => DB::table('expedientes')->insertGetId(Registros::expediente([
        'tipo_procedimiento_tupa_id' => $tupa, 'fecha_alerta' => $alerta, 'fecha_vencimiento' => $vencimiento,
    ]));

    $expediente('2026-11-05', '2026-11-10');                 // en trámite, aún sin alerta
    $expediente('2026-10-27', '2026-10-30');                 // por vencer (ya pasó su alerta)
    $expediente('2026-10-15', '2026-10-20');                 // vencido
    $atendido = $expediente('2026-10-15', '2026-10-20');     // ya tiene licencia: no cuenta
    DB::table('licencias_funcionamiento')->insert(Registros::licencia(['expediente_id' => $atendido, 'fecha_emision' => '2025-01-10']));
    $rechazado = $expediente('2026-10-15', '2026-10-20');    // improcedente: no cuenta
    DB::table('autorizaciones_improcedentes')->insert(Registros::autorizacionImprocedente(['expediente_id' => $rechazado]));

    $this->actingAs(($this->personal)(), 'api')->getJson(URL_RESUMEN)->assertOk()
        ->assertJsonPath('expedientes', ['en_tramite' => 3, 'por_vencer' => 1, 'vencidos' => 1]);
});

it('cuenta licencias vigentes, emitidas en el mes y el total', function () {
    DB::table('licencias_funcionamiento')->insert(Registros::licencia([          // vigente, emitida este mes
        'fecha_emision' => '2026-10-05', 'es_vigencia_indeterminada' => true,
    ]));
    DB::table('licencias_funcionamiento')->insert(Registros::licencia([          // vencida
        'fecha_emision' => '2025-03-01', 'es_vigencia_indeterminada' => false, 'fecha_fin_vigencia' => '2026-09-01',
    ]));
    $clausurada = DB::table('licencias_funcionamiento')->insertGetId(Registros::licencia([ // inactiva, emitida este mes
        'fecha_emision' => '2026-10-10',
    ]));
    DB::table('licencias_funcionamiento_estados')->insert([
        'licencia_funcionamiento_id' => $clausurada, 'estado_id' => $this->estadoInactivo, 'fecha_estado' => now(),
        'documento' => 'RES', 'observaciones' => '', 'fecha_digitacion' => now(), 'usuario_id' => 1,
    ]);

    $this->actingAs(($this->personal)(), 'api')->getJson(URL_RESUMEN)->assertOk()
        ->assertJsonPath('licencias', ['vigentes' => 1, 'emitidas_mes' => 2, 'total' => 3]);
});

it('cuenta ITSE vigentes, por vencer en 30 días y emitidos en el mes', function () {
    $itse = fn (string $expedicion, string $caducidad, array $extra = []) => DB::table('itse')->insertGetId(Registros::itse(array_merge([
        'fecha_expedicion' => $expedicion, 'fecha_solicitud_renovacion' => $expedicion, 'fecha_caducidad' => $caducidad,
    ], $extra)));

    $itse('2026-10-02', '2028-10-02');                                   // vigente, emitido este mes
    $itse('2024-11-15', '2026-11-15');                                   // vigente y por vencer
    $original = $itse('2024-11-10', '2026-11-10');                       // ya renovado: no cuenta
    $itse('2026-10-20', '2028-10-20', ['itse_principal_id' => $original]); // su renovación: vigente, este mes
    $clausurado = $itse('2024-11-01', '2026-11-01');                     // inactivo: no cuenta
    DB::table('itse_estados')->insert([
        'itse_id' => $clausurado, 'estado_id' => $this->estadoInactivo, 'fecha_estado' => now(),
        'documento' => 'RES', 'observaciones' => '', 'fecha_digitacion' => now(), 'usuario_id' => 1,
    ]);
    $itse('2024-10-01', '2026-10-01');                                   // vencido

    $this->actingAs(($this->personal)(), 'api')->getJson(URL_RESUMEN)->assertOk()
        ->assertJsonPath('itse', ['vigentes' => 3, 'por_vencer' => 1, 'emitidos_mes' => 2, 'dias_alerta' => 30]);
});

it('lo ve todo el personal aunque no tenga acceso a los módulos, pero no un ciudadano', function () {
    $this->getJson(URL_RESUMEN)->assertUnauthorized();

    $this->actingAs(($this->personal)(), 'api')->getJson(URL_RESUMEN)->assertOk();

    $ciudadanoId = DB::table('auth_user')->insertGetId(\Tests\Fixtures\UsuarioAuth::datos(['username' => 'vecino', 'is_staff' => false]));
    $this->actingAs(User::find($ciudadanoId), 'api')->getJson(URL_RESUMEN)->assertForbidden();
});

it('solo devuelve cifras, sin datos de trámites ni personas', function () {
    DB::table('licencias_funcionamiento')->insert(Registros::licencia(['nombre_comercial' => 'BODEGA SECRETA', 'fecha_emision' => '2026-10-05']));

    $respuesta = $this->actingAs(($this->personal)(), 'api')->getJson(URL_RESUMEN)->assertOk();

    expect($respuesta->getContent())->not->toContain('BODEGA SECRETA');
    expect(array_keys($respuesta->json()))->toBe(['expedientes', 'licencias', 'itse']);
});
