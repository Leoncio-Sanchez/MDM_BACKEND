<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

const URL_CONFIG_INFORME = '/api/lf-itse/configuracion/informes/';

beforeEach(function () {
    $this->personal = function (array $accesos): User {
        $id = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(['username' => 'p_'.uniqid(), 'is_staff' => true]));
        DB::table('usuarios_perfiles')->insert(array_merge(
            ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false],
            $accesos,
            ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
        ));

        return User::find($id);
    };
});

it('devuelve los valores por defecto de la migración', function () {
    $this->actingAs(($this->personal)(['admin' => true]), 'api')
        ->getJson(URL_CONFIG_INFORME)
        ->assertOk()
        ->assertExactJson([
            'entidad' => 'MUNICIPALIDAD DISTRITAL DE MORALES - PROVINCIA DE SAN MARTÍN',
            'nombre_anio' => '',
            'gerencia' => 'Gerencia de Desarrollo Económico y Social',
            'siglas' => 'MDM/GDES',
            'firmante_nombre' => '',
            'firmante_cargo' => 'Subgerente de Desarrollo Económico',
        ]);
});

it('guarda los cambios y registra quién los hizo', function () {
    $admin = ($this->personal)(['admin' => true]);
    $datos = [
        'entidad' => 'MUNICIPALIDAD DISTRITAL DE MORALES - PROVINCIA DE SAN MARTÍN',
        'nombre_anio' => '  Año de la Esperanza  ',
        'gerencia' => 'Gerencia de Desarrollo Económico y Social',
        'siglas' => 'MDM/GDES-SGDE',
        'firmante_nombre' => 'JUAN PÉREZ',
        'firmante_cargo' => 'Subgerente de Desarrollo Económico',
    ];

    $this->actingAs($admin, 'api')->putJson(URL_CONFIG_INFORME, $datos)
        ->assertOk()
        ->assertJson(['nombre_anio' => 'Año de la Esperanza', 'siglas' => 'MDM/GDES-SGDE']);

    expect(DB::table('configuracion_sistema')->where('clave', 'informe.firmante_nombre')->first())
        ->valor->toBe('JUAN PÉREZ')
        ->usuario_id->toBe($admin->id);
});

it('valida los campos', function () {
    $this->actingAs(($this->personal)(['admin' => true]), 'api')
        ->putJson(URL_CONFIG_INFORME, ['entidad' => '', 'gerencia' => 'G', 'siglas' => 'mdm gdes', 'firmante_cargo' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['entidad', 'siglas', 'firmante_cargo']);
});

it('solo la administra quien tiene el acceso de Administración', function () {
    $this->getJson(URL_CONFIG_INFORME)->assertUnauthorized();
    $this->actingAs(($this->personal)(['licencias' => true]), 'api')->getJson(URL_CONFIG_INFORME)->assertForbidden();
    $this->actingAs(($this->personal)(['licencias' => true]), 'api')->putJson(URL_CONFIG_INFORME, [])->assertForbidden();
});
