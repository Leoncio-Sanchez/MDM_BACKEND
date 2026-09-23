<?php
declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    DB::table('auth_user')->insert([
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => 'secret',
    ]);
    $this->user = User::find(1);

    DB::table('unidades_organicas')->insert([
        'id' => 1,
        'codigo' => 'UO-01',
        'nombre' => 'Unidad 1',
        'esta_activo' => true,
    ]);

    DB::table('tipos_procedimiento_tupa')->insert([
        'id' => 1,
        'codigo' => 'TUPA-01',
        'nombre' => 'Procedimiento TUPA 1',
        'plazo_atencion_dias' => 10,
        'dias_alerta_vencimiento' => 3,
        'esta_activo' => true,
        'requiere_lf' => true,
        'requiere_itse' => true,
        'fecha_digitacion' => '2026-01-01 00:00:00',
        'usuario_id' => 1,
        'unidad_organica_id' => 1,
        'monto' => 50.00,
    ]);

    DB::table('personas')->insert([
        'id' => 1,
        'tipo_persona' => 'N',
        'sexo' => 'M',
        'nombres' => 'Juan',
        'apellido_paterno' => 'Perez',
        'apellido_materno' => 'Gomez',
        'user_id' => 1,
    ]);

    DB::table('personas')->insert([
        'id' => 2,
        'tipo_persona' => 'N',
        'sexo' => 'F',
        'nombres' => 'Maria',
        'apellido_paterno' => 'Lopez',
        'apellido_materno' => 'Diaz',
        'user_id' => 1,
    ]);
});

it('POST /api/lf-itse/expedientes/ crea un expediente correctamente y retorna 201 con 19 keys', function () {
    $payload = [
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
        'representante_id' => 2,
        'observaciones' => 'Expediente de prueba inicial',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/', $payload);

    $response->assertStatus(201);

    $json = $response->json();
    $expectedKeys = [
        'id', 'archivos', 'autorizaciones_improcedentes', 'uuid', 'numero_expediente',
        'fecha_recepcion', 'observaciones', 'fecha_vencimiento', 'fecha_alerta',
        'fecha_suspension', 'dias_ampliacion', 'motivo_ampliacion',
        'fecha_digitacion_ampliacion', 'fecha_digitacion',
        'tipo_procedimiento_tupa', 'solicitante', 'representante',
        'usuario_ampliacion', 'usuario',
    ];

    foreach ($expectedKeys as $key) {
        expect(array_key_exists($key, $json))->toBeTrue("Falta key: {$key}");
    }

    expect($json['numero_expediente'])->toBe(1)
        ->and($json['tipo_procedimiento_tupa'])->toBe(1)
        ->and($json['solicitante'])->toBe(1)
        ->and($json['representante'])->toBe(2)
        ->and($json['usuario'])->toBe(1)
        ->and($json['archivos'])->toBe([])
        ->and($json['autorizaciones_improcedentes'])->toBe([])
        ->and($json['observaciones'])->toBe('Expediente de prueba inicial');
});

it('POST /api/lf-itse/expedientes/ crea con numero_expediente explicito', function () {
    $payload = [
        'numero_expediente' => 999,
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/', $payload);

    $response->assertStatus(201);
    expect($response->json('numero_expediente'))->toBe(999);
});

it('POST /api/lf-itse/expedientes/ falla sin autenticacion retornando 401', function () {
    $response = $this->postJson('/api/lf-itse/expedientes/', [
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
    ]);

    $response->assertStatus(401);
});

it('POST /api/lf-itse/expedientes/ falla con datos invalidos retornando 422', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/', [
            'tipo_procedimiento_tupa_id' => 9999, // no existe
            'solicitante_id' => 9999, // no existe
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['fecha_recepcion', 'tipo_procedimiento_tupa_id', 'solicitante_id']);
});

it('POST /api/lf-itse/expedientes/ retorna 409 ante numero duplicado en el mismo anio', function () {
    $payload = [
        'numero_expediente' => 500,
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
    ];

    $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/', $payload)
        ->assertStatus(201);

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/', $payload);

    $response->assertStatus(409);
});

it('GET /api/lf-itse/expedientes/ lista expedientes retornando 200 con 8 keys (TD-011)', function () {
    $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/expedientes/', [
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
    ])->assertStatus(201);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/');

    $response->assertStatus(200);

    $items = $response->json();
    expect($items)->toBeArray()
        ->and(count($items))->toBeGreaterThanOrEqual(1);

    $first = $items[0];
    $expectedKeys = [
        'id', 'uuid', 'numero_expediente', 'fecha_recepcion',
        'fecha_vencimiento', 'fecha_alerta', 'tipo_procedimiento_tupa', 'solicitante',
    ];

    foreach ($expectedKeys as $key) {
        expect(array_key_exists($key, $first))->toBeTrue("Falta key de list: {$key}");
    }
    expect(count($first))->toBe(8);
});

it('GET /api/lf-itse/expedientes/ falla sin autenticacion retornando 401', function () {
    $response = $this->getJson('/api/lf-itse/expedientes/');
    $response->assertStatus(401);
});

it('GET /api/lf-itse/expedientes/{pk}/ obtiene expediente existente retornando 200 con 19 keys (TD-012)', function () {
    $createRes = $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/expedientes/', [
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
        'observaciones' => 'Para detalle',
    ]);
    $id = $createRes->json('id');

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/lf-itse/expedientes/{$id}/");

    $response->assertStatus(200);

    $json = $response->json();
    $expectedKeys = [
        'id', 'archivos', 'autorizaciones_improcedentes', 'uuid', 'numero_expediente',
        'fecha_recepcion', 'observaciones', 'fecha_vencimiento', 'fecha_alerta',
        'fecha_suspension', 'dias_ampliacion', 'motivo_ampliacion',
        'fecha_digitacion_ampliacion', 'fecha_digitacion',
        'tipo_procedimiento_tupa', 'solicitante', 'representante',
        'usuario_ampliacion', 'usuario',
    ];

    foreach ($expectedKeys as $key) {
        expect(array_key_exists($key, $json))->toBeTrue("Falta key: {$key}");
    }
    expect(count($json))->toBe(19);
});

it('GET /api/lf-itse/expedientes/{pk}/ retorna 404 cuando no existe', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/99999/');

    $response->assertStatus(404);
});

it('GET /api/lf-itse/expedientes/{pk}/ falla sin autenticacion retornando 401', function () {
    $response = $this->getJson('/api/lf-itse/expedientes/1/');
    $response->assertStatus(401);
});

it('PUT /api/lf-itse/expedientes/{pk}/ actualiza expediente y retorna 200 con 19 keys', function () {
    $createRes = $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/expedientes/', [
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
    ]);
    $id = $createRes->json('id');

    $updatePayload = [
        'numero_expediente' => 10,
        'fecha_recepcion' => '2026-03-12T09:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
        'representante_id' => 2,
        'observaciones' => 'Observacion modificada',
    ];

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/lf-itse/expedientes/{$id}/", $updatePayload);

    $response->assertStatus(200);

    $json = $response->json();
    expect($json['numero_expediente'])->toBe(10)
        ->and($json['observaciones'])->toBe('Observacion modificada')
        ->and($json['representante'])->toBe(2)
        ->and(count($json))->toBe(19);
});

it('PUT /api/lf-itse/expedientes/{pk}/ falla sin autenticacion retornando 401', function () {
    $response = $this->putJson('/api/lf-itse/expedientes/1/', [
        'numero_expediente' => 10,
        'fecha_recepcion' => '2026-03-12T09:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
    ]);

    $response->assertStatus(401);
});

it('PUT /api/lf-itse/expedientes/{pk}/ retorna 404 cuando no existe', function () {
    $response = $this->actingAs($this->user, 'api')
        ->putJson('/api/lf-itse/expedientes/99999/', [
            'numero_expediente' => 10,
            'fecha_recepcion' => '2026-03-12T09:00:00',
            'tipo_procedimiento_tupa_id' => 1,
            'solicitante_id' => 1,
        ]);

    $response->assertStatus(404);
});

it('DELETE /api/lf-itse/expedientes/{pk}/ elimina expediente correctamente y retorna 204', function () {
    $createRes = $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/expedientes/', [
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
    ]);
    $id = $createRes->json('id');

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/lf-itse/expedientes/{$id}/");

    $response->assertStatus(204);

    // Verificar que fue eliminado
    $checkRes = $this->actingAs($this->user, 'api')
        ->getJson("/api/lf-itse/expedientes/{$id}/");
    $checkRes->assertStatus(404);
});

it('DELETE /api/lf-itse/expedientes/{pk}/ falla sin autenticacion retornando 401', function () {
    $response = $this->deleteJson('/api/lf-itse/expedientes/1/');
    $response->assertStatus(401);
});

it('DELETE /api/lf-itse/expedientes/{pk}/ retorna 404 cuando no existe', function () {
    $response = $this->actingAs($this->user, 'api')
        ->deleteJson('/api/lf-itse/expedientes/99999/');

    $response->assertStatus(404);
});

it('DELETE /api/lf-itse/expedientes/{pk}/ retorna 409 cuando tiene licencia asociada', function () {
    $createRes = $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/expedientes/', [
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
    ]);
    $id = $createRes->json('id');

    DB::table('licencias_funcionamiento')->insert([
        'id' => 1,
        'uuid' => '11111111-1111-1111-1111-111111111111',
        'numero_licencia' => 101,
        'fecha_emision' => '2026-03-15',
        'nombre_comercial' => 'Mi Negocio',
        'titular_id' => 1,
        'conductor_id' => 1,
        'expediente_id' => $id,
        'tipo_licencia_id' => 1,
        'nivel_riesgo_id' => 1,
        'zonificacion_id' => 1,
        'es_vigencia_indeterminada' => true,
        'actividad' => 'Ventas',
        'direccion' => 'Av Principal 123',
        'hora_desde' => 8,
        'hora_hasta' => 20,
        'resolucion_numero' => 'RES-101',
        'area' => 50.00,
        'numero_recibo_pago' => 'REC-101',
        'estado' => 'EMITIDA',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/lf-itse/expedientes/{$id}/");

    $response->assertStatus(409);
});

it('DELETE /api/lf-itse/expedientes/{pk}/ retorna 409 cuando tiene ITSE asociado', function () {
    $createRes = $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/expedientes/', [
        'fecha_recepcion' => '2026-03-10T10:00:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 1,
    ]);
    $id = $createRes->json('id');

    DB::table('itse')->insert([
        'id' => 1,
        'uuid' => '22222222-2222-2222-2222-222222222222',
        'numero_itse' => 201,
        'fecha_expedicion' => '2026-03-15',
        'fecha_caducidad' => '2028-03-15',
        'nombre_comercial' => 'ITSE Local',
        'direccion' => 'Av Principal 123',
        'resolucion_numero' => 'RES-ITSE-01',
        'area' => 100.00,
        'capacidad_aforo' => 20,
        'numero_recibo_pago' => 'REC-ITSE-01',
        'tipo_itse_id' => 1,
        'expediente_id' => $id,
        'usuario_id' => 1,
        'nivel_riesgo_id' => 1,
        'conductor_id' => 1,
        'titular_id' => 1,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/lf-itse/expedientes/{$id}/");

    $response->assertStatus(409);
});
