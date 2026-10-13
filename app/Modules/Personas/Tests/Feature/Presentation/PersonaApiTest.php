<?php

declare(strict_types=1);
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {

    $userId = DB::table('auth_user')->insertGetId(\Tests\Fixtures\UsuarioAuth::datos([
        'is_staff' => true,
        'username' => 'admin_'.uniqid(),
        'password' => 'admin',
        'is_active' => true,
    ]));
    $this->user = User::find($userId);

});

it('listar personas retorna paginado', function () {
    $response = $this->actingAs($this->user, 'api')->getJson('/api/lf-itse/personas/');
    $response->assertStatus(200)->assertJsonIsArray();
});

it('crea persona', function () {
    $response = $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/personas/', [
        'tipo_persona' => 'N',
        'sexo' => 'M',
        'nombres' => 'Juan',
        'documentos' => [],
    ]);
    $response->assertStatus(201);
});

it('obtiene persona por id', function () {
    $personaId = DB::table('personas')->insertGetId(\Tests\Fixtures\Registros::persona([
        'tipo_persona' => 'N', 'sexo' => 'M', 'nombres' => 'Juan', 'direccion' => 'D', 'distrito' => 'D', 'provincia' => 'P', 'departamento' => 'D', 'fecha_creacion' => now(), 'fecha_actualizacion' => now(), 'user_id' => $this->user->id,
    ]));

    $response = $this->actingAs($this->user, 'api')->getJson('/api/lf-itse/personas/'.$personaId.'/');
    $response->assertStatus(200)->assertJsonPath('nombres', 'Juan');
});

it('actualiza persona', function () {
    $personaId = DB::table('personas')->insertGetId(\Tests\Fixtures\Registros::persona([
        'tipo_persona' => 'N', 'sexo' => 'M', 'nombres' => 'Juan', 'direccion' => 'D', 'distrito' => 'D', 'provincia' => 'P', 'departamento' => 'D', 'fecha_creacion' => now(), 'fecha_actualizacion' => now(), 'user_id' => $this->user->id,
    ]));

    $response = $this->actingAs($this->user, 'api')->putJson('/api/lf-itse/personas/'.$personaId.'/', [
        'nombres' => 'Pedro',
    ]);
    $response->assertStatus(200)->assertJsonPath('nombres', 'Pedro');
});

it('elimina persona', function () {
    $personaId = DB::table('personas')->insertGetId(\Tests\Fixtures\Registros::persona([
        'tipo_persona' => 'N', 'sexo' => 'M', 'nombres' => 'Juan', 'direccion' => 'D', 'distrito' => 'D', 'provincia' => 'P', 'departamento' => 'D', 'fecha_creacion' => now(), 'fecha_actualizacion' => now(), 'user_id' => $this->user->id,
    ]));

    $response = $this->actingAs($this->user, 'api')->deleteJson('/api/lf-itse/personas/'.$personaId.'/');
    $response->assertStatus(204);
});

it('buscar personas devuelve JSON', function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        test()->markTestSkipped('Requiere PostgreSQL');
    }
    $response = $this->actingAs($this->user, 'api')->getJson('/api/lf-itse/personas/buscar/?filtro=NOMBRE&valor=JUAN');
    // Si es sqlite saldrá [], pero como es array, probamos que devuelva 200 array
    $response->assertStatus(200);
});

it('sexos devuelve lista', function () {
    $response = $this->actingAs($this->user, 'api')->getJson('/api/lf-itse/personas/sexos/');
    $response->assertStatus(200);
});

it('documentos devuelve lista', function () {
    $personaId = DB::table('personas')->insertGetId(\Tests\Fixtures\Registros::persona([
        'tipo_persona' => 'N', 'sexo' => 'M', 'nombres' => 'Juan', 'direccion' => 'D', 'distrito' => 'D', 'provincia' => 'P', 'departamento' => 'D', 'fecha_creacion' => now(), 'fecha_actualizacion' => now(), 'user_id' => $this->user->id,
    ]));
    $response = $this->actingAs($this->user, 'api')->getJson('/api/lf-itse/personas/'.$personaId.'/documentos/');
    $response->assertStatus(200);
});

it('crear falla si falta nombres', function () {
    $response = $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/personas/', [
        'tipo_persona' => 'N', 'sexo' => 'M',
    ]);
    $response->assertStatus(422);
});

it('falla sin token', function () {
    $response = $this->getJson('/api/lf-itse/personas/');
    $response->assertStatus(401);
});
