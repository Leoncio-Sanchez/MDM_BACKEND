<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    DB::table('auth_user')->insert(UsuarioAuth::datos(['id' => 1, 'username' => 'admin', 'is_staff' => true, 'is_superuser' => true]));
    $this->admin = User::find(1);
});

it('lista a las personas heredadas con un sexo que el sistema no reconoce', function (string $guardado, string $mostrado) {
    $id = DB::table('personas')->insertGetId(Registros::persona(['sexo' => $guardado]));

    $this->actingAs($this->admin, 'api')->getJson('/api/lf-itse/personas/')->assertOk();
    $this->actingAs($this->admin, 'api')->getJson("/api/lf-itse/personas/{$id}/")->assertOk()->assertJsonPath('sexo', $mostrado);

    // Leerla no cambia lo que vino del sistema anterior
    expect(DB::table('personas')->where('id', $id)->value('sexo'))->toBe($guardado);
})->with([
    'vacío' => ['', 'X'],
    'con espacios' => [' ', 'X'],
    'desconocido' => ['Z', 'X'],
    'en minúscula' => ['f', 'F'],
    'válido' => ['M', 'M'],
]);

it('rechaza con un error de validación un sexo o tipo de persona que no existe', function (string $campo) {
    $datos = ['tipo_persona' => 'N', 'sexo' => 'M', 'nombres' => 'PRUEBA'];

    $this->actingAs($this->admin, 'api')->postJson('/api/lf-itse/personas/', [...$datos, $campo => 'Z'])
        ->assertStatus(422)->assertJsonValidationErrors($campo);
    expect(DB::table('personas')->count())->toBe(0);

    $id = DB::table('personas')->insertGetId(Registros::persona(['sexo' => 'M', 'tipo_persona' => 'N']));
    $this->actingAs($this->admin, 'api')->putJson("/api/lf-itse/personas/{$id}/", [...$datos, $campo => 'Z'])
        ->assertStatus(422)->assertJsonValidationErrors($campo);
    expect(DB::table('personas')->where('id', $id)->value($campo))->toBe($datos[$campo]);
})->with(['sexo', 'tipo_persona']);
