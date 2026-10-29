<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Ninguna prueba debe salir a PIDE con credenciales reales
    Http::preventStrayRequests();

    $this->usuario = function (array $perfil): User {
        $id = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(['username' => 'u_'.uniqid(), 'is_staff' => true]));
        DB::table('usuarios_perfiles')->insert(array_merge(
            ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false,
                'es_inspector' => false, 'es_jefe_itse' => false, 'reportes_completos' => false],
            $perfil,
            ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
        ));

        return User::find($id);
    };
});

it('las oficinas que tramitan y TI ven y registran personas', function (string $acceso) {
    $quien = ($this->usuario)([$acceso => true]);

    $this->actingAs($quien, 'api')->getJson('/api/lf-itse/personas/')->assertOk();
    $this->actingAs($quien, 'api')->postJson('/api/lf-itse/personas/', [])->assertUnprocessable();
})->with(['expedientes', 'licencias', 'itse', 'admin']);

it('quien solo ve reportes no accede a datos personales ni consulta RENIEC o SUNAT', function () {
    $auditor = ($this->usuario)(['reportes_completos' => true]);

    $this->actingAs($auditor, 'api')->getJson('/api/lf-itse/personas/')->assertForbidden();
    $this->actingAs($auditor, 'api')->getJson('/api/lf-itse/personas/buscar/?filtro=DNI&valor=12345678')->assertForbidden();
    $this->actingAs($auditor, 'api')->postJson('/api/lf-itse/personas/', [])->assertForbidden();
    $this->actingAs($auditor, 'api')->deleteJson('/api/lf-itse/personas/1/')->assertForbidden();
    $this->actingAs($auditor, 'api')->getJson('/api/lf-itse/reniec/consultar/?dni=12345678')->assertForbidden();
    $this->actingAs($auditor, 'api')->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789')->assertForbidden();
});

it('el personal sin ningún acceso tampoco', function () {
    $sinAccesos = ($this->usuario)([]);

    $this->actingAs($sinAccesos, 'api')->getJson('/api/lf-itse/personas/')->assertForbidden();
    $this->actingAs($sinAccesos, 'api')->getJson('/api/lf-itse/reniec/consultar/?dni=12345678')->assertForbidden();
});
