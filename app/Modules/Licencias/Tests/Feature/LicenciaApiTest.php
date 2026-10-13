<?php
declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;

// TestCase y RefreshDatabase se aplican desde tests/Pest.php

const URL_LICENCIAS = '/api/lf-itse/licencias-funcionamiento';

beforeEach(function () {
    DB::table('auth_user')->insert(\Tests\Fixtures\UsuarioAuth::datos(['is_staff' => true, 'id' => 1, 'username' => 'test', 'email' => 'test@test.com', 'password' => 'secret']));
    $this->user = User::find(1);

    DB::table('tipos_licencia')->insert(\Tests\Fixtures\Registros::tipoLicencia(['id' => 1, 'nombre' => 'INDETERMINADA']));
    DB::table('personas')->insert([\Tests\Fixtures\Registros::persona(['id' => 1, 'nombres' => 'TITULAR']), \Tests\Fixtures\Registros::persona(['id' => 2, 'nombres' => 'CONDUCTOR'])]);
    DB::table('expedientes')->insert([\Tests\Fixtures\Registros::expediente(['id' => 1]), \Tests\Fixtures\Registros::expediente(['id' => 2])]);
    DB::table('niveles_riesgo')->insert(\Tests\Fixtures\Registros::nivelRiesgo(['id' => 1, 'codigo' => 'B', 'nombre' => 'BAJO']));
    DB::table('zonificaciones')->insert(\Tests\Fixtures\Registros::zonificacion(['id' => 1, 'codigo' => 'CZ', 'nombre' => 'COMERCIO ZONAL']));
    DB::table('estados')->insert(\Tests\Fixtures\Registros::estado(['id' => 1, 'nombre' => 'CLAUSURA', 'es_para_lf' => true, 'es_para_itse' => false, 'esta_activo' => false]));
});

function datosLicencia(array $cambios = []): array
{
    return [
        'numero_licencia' => 1001,
        'nombre_comercial' => 'BODEGA SANTA ROSA',
        'tipo_licencia_id' => 1,
        'titular_id' => 1,
        'conductor_id' => 2,
        'expediente_id' => 1,
        'nivel_riesgo_id' => 1,
        'zonificacion_id' => 1,
        'actividad' => 'VENTA DE ABARROTES',
        'direccion' => 'AV. GRAU 450',
        'hora_desde' => 8,
        'hora_hasta' => 22,
        'resolucion_numero' => 'RES-001-2026',
        'area' => 85.5,
        'numero_recibo_pago' => 'REC-001',
        'es_vigencia_indeterminada' => true,
        ...$cambios,
    ];
}

it('rechaza todas las operaciones sin autenticación', function () {
    $this->getJson(URL_LICENCIAS . '/consulta')->assertUnauthorized();
    $this->getJson(URL_LICENCIAS . '/reporte')->assertUnauthorized();
    $this->postJson(URL_LICENCIAS, datosLicencia())->assertUnauthorized();
    $this->putJson(URL_LICENCIAS . '/1', datosLicencia())->assertUnauthorized();
    $this->deleteJson(URL_LICENCIAS . '/1')->assertUnauthorized();
    $this->postJson(URL_LICENCIAS . '/inactivar', [])->assertUnauthorized();

    $this->assertDatabaseCount('licencias_funcionamiento', 0);
});

it('crea una licencia guardando todos los datos enviados', function () {
    $this->actingAs($this->user, 'api')
        ->postJson(URL_LICENCIAS, datosLicencia())
        ->assertCreated()
        ->assertJsonPath('numero_licencia', 1001)
        ->assertJsonPath('actividad', 'VENTA DE ABARROTES');

    $this->assertDatabaseHas('licencias_funcionamiento', [
        'numero_licencia' => 1001,
        'usuario_id' => 1, // auditoría: obligatoria en el esquema real
        'conductor_id' => 2,
        'actividad' => 'VENTA DE ABARROTES',
        'direccion' => 'AV. GRAU 450',
        'hora_desde' => 8,
        'hora_hasta' => 22,
        'resolucion_numero' => 'RES-001-2026',
        'numero_recibo_pago' => 'REC-001',
    ]);
    expect((float) DB::table('licencias_funcionamiento')->value('area'))->toBe(85.5);
});

it('rechaza con 422 si faltan datos obligatorios', function () {
    $this->actingAs($this->user, 'api')
        ->postJson(URL_LICENCIAS, ['numero_licencia' => 1001, 'nombre_comercial' => 'X', 'tipo_licencia_id' => 1])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['titular_id', 'expediente_id', 'actividad', 'direccion', 'hora_desde', 'hora_hasta', 'area', 'numero_recibo_pago']);
});

it('modifica una licencia reemplazando sus datos', function () {
    $id = $this->actingAs($this->user, 'api')->postJson(URL_LICENCIAS, datosLicencia())->json('id');

    $this->actingAs($this->user, 'api')
        ->putJson(URL_LICENCIAS . "/{$id}", datosLicencia(['nombre_comercial' => 'MINIMARKET SANTA ROSA', 'hora_hasta' => 23]))
        ->assertOk()
        ->assertJsonPath('nombre_comercial', 'MINIMARKET SANTA ROSA');

    $this->assertDatabaseHas('licencias_funcionamiento', [
        'id' => $id,
        'nombre_comercial' => 'MINIMARKET SANTA ROSA',
        'hora_hasta' => 23,
        'actividad' => 'VENTA DE ABARROTES',
    ]);
});

it('inactiva una licencia registrando el sustento y el usuario', function () {
    $id = $this->actingAs($this->user, 'api')->postJson(URL_LICENCIAS, datosLicencia())->json('id');

    $this->actingAs($this->user, 'api')
        ->postJson(URL_LICENCIAS . '/inactivar', [
            'licencia_id' => $id,
            'estado_id' => 1,
            'fecha_estado' => '2026-10-12',
            'documento' => 'RES 120-2026',
            'observaciones' => 'Clausura',
        ])
        ->assertOk()
        ->assertJsonPath('id', $id);

    $this->assertDatabaseHas('licencias_funcionamiento_estados', [
        'licencia_funcionamiento_id' => $id,
        'estado_id' => 1,
        'documento' => 'RES 120-2026',
        'observaciones' => 'Clausura',
        'usuario_id' => 1,
    ]);
});

it('responde 409 si se registra dos veces el mismo estado de inactivación', function () {
    $id = $this->actingAs($this->user, 'api')->postJson(URL_LICENCIAS, datosLicencia())->json('id');
    $datos = ['licencia_id' => $id, 'estado_id' => 1, 'fecha_estado' => '2026-10-12', 'documento' => 'D', 'observaciones' => 'O'];

    $this->actingAs($this->user, 'api')->postJson(URL_LICENCIAS . '/inactivar', $datos)->assertOk();
    $this->actingAs($this->user, 'api')->postJson(URL_LICENCIAS . '/inactivar', $datos)->assertStatus(409);
});

it('responde 409 si el número de licencia ya existe', function () {
    $this->actingAs($this->user, 'api')->postJson(URL_LICENCIAS, datosLicencia())->assertCreated();

    $this->actingAs($this->user, 'api')
        ->postJson(URL_LICENCIAS, datosLicencia(['expediente_id' => 2, 'numero_recibo_pago' => 'REC-999']))
        ->assertStatus(409)
        ->assertJsonStructure(['error']);
});

it('devuelve fecha_digitacion del historial en formato ISO 8601', function () {
    $id = $this->actingAs($this->user, 'api')->postJson(URL_LICENCIAS, datosLicencia())->json('id');

    $fecha = $this->actingAs($this->user, 'api')
        ->postJson(URL_LICENCIAS . '/inactivar', ['licencia_id' => $id, 'estado_id' => 1, 'fecha_estado' => '2026-10-12', 'documento' => 'D', 'observaciones' => 'O'])
        ->json('historial_estados.0.fecha_digitacion');

    expect($fecha)->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}[+-]\d{2}:\d{2}$/');
});
