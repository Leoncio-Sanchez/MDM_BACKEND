<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Seguridad\Domain\Contracts\RevisionAccesosRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Casillas nuevas del perfil: es_inspector, es_jefe_itse y reportes_completos.
 */
beforeEach(function () {
    /** Usuario con las casillas indicadas (el resto en false) */
    $this->usuario = function (array $casillas = [], array $datos = []): User {
        $id = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(array_merge(['username' => 'u_'.uniqid(), 'is_staff' => true], $datos)));
        DB::table('usuarios_perfiles')->insert(array_merge(
            ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false,
                'es_inspector' => false, 'es_jefe_itse' => false, 'reportes_completos' => false],
            $casillas,
            ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
        ));

        return User::find($id);
    };
});

it('el perfil del usuario actual incluye las casillas nuevas', function () {
    $this->actingAs(($this->usuario)(['itse' => true, 'es_jefe_itse' => true]), 'api')
        ->getJson('/api/auth/users/me/')
        ->assertOk()
        ->assertJsonPath('permisos', [
            'expedientes' => false, 'licencias' => false, 'itse' => true, 'admin' => false,
            'inspector' => false, 'jefe_itse' => true, 'reportes' => false,
        ]);
});

it('el superusuario es jefe y ve reportes, pero no es inspector', function () {
    $this->actingAs(($this->usuario)([], ['is_superuser' => true]), 'api')
        ->getJson('/api/auth/users/me/')
        ->assertOk()
        ->assertJsonPath('permisos.jefe_itse', true)
        ->assertJsonPath('permisos.reportes', true)
        ->assertJsonPath('permisos.inspector', false);
});

describe('administración de usuarios', function () {
    it('guarda y devuelve las casillas nuevas', function () {
        $r = $this->actingAs(($this->usuario)(['admin' => true]), 'api')
            ->postJson('/api/lf-itse/usuarios', [
                'username' => 'auditor', 'password' => 'clave-segura-123', 'is_staff' => true,
                'perfil' => ['reportes_completos' => true],
            ])
            ->assertCreated();

        expect($r->json('perfil'))->toBe([
            'expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false,
            'es_inspector' => false, 'es_jefe_itse' => false, 'reportes_completos' => true,
        ]);
    });

    it('el jefe de ITSE necesita el acceso ITSE, también al editar solo una casilla', function () {
        $admin = ($this->usuario)(['admin' => true]);

        $this->actingAs($admin, 'api')
            ->postJson('/api/lf-itse/usuarios', ['username' => 'jefe', 'perfil' => ['es_jefe_itse' => true]])
            ->assertUnprocessable()
            ->assertJson(['error' => 'El jefe de ITSE necesita también el acceso de ITSE.']);
        expect(DB::table('auth_user')->where('username', 'jefe')->exists())->toBeFalse();

        $jefe = ($this->usuario)(['itse' => true, 'es_jefe_itse' => true]);
        $this->actingAs($admin, 'api')
            ->putJson("/api/lf-itse/usuarios/{$jefe->id}", ['perfil' => ['itse' => false]])
            ->assertUnprocessable();
        expect(DB::table('usuarios_perfiles')->where('user_id', $jefe->id)->value('itse'))->toBeTrue();
    });
});

describe('reportes completos (Subgerente, Auditor)', function () {
    it('lee todos los reportes y sus listados de apoyo', function () {
        $auditor = ($this->usuario)(['reportes_completos' => true]);
        $rango = '?desde=2026-10-01&hasta=2026-10-31';

        foreach ([
            '/api/lf-itse/reportes/licencias/'.$rango,
            '/api/lf-itse/reportes/bajas/licencias/'.$rango,
            '/api/lf-itse/reportes/bajas/itse/'.$rango,
            '/api/lf-itse/reportes/improcedentes/licencias/'.$rango,
            '/api/lf-itse/reportes/improcedentes/itse/'.$rango,
            '/api/lf-itse/itse/por-renovar/?dias=30',
            '/api/lf-itse/expedientes/pendientes/',
            '/api/lf-itse/dashboard/resumen/',
        ] as $url) {
            $this->actingAs($auditor, 'api')->getJson($url)->assertOk();
        }
    });

    it('no entra a los módulos de trabajo ni puede modificar', function () {
        $auditor = ($this->usuario)(['reportes_completos' => true]);

        $this->actingAs($auditor, 'api')->getJson('/api/lf-itse/expedientes/')->assertForbidden();
        $this->actingAs($auditor, 'api')->getJson('/api/lf-itse/itse/buscar/')->assertForbidden();
        $this->actingAs($auditor, 'api')->getJson('/api/lf-itse/licencias-funcionamiento/buscar?filtro=NOMBRE_COMERCIAL&valor=x')->assertForbidden();
        $this->actingAs($auditor, 'api')->postJson('/api/lf-itse/expedientes/', [])->assertForbidden();
        $this->actingAs($auditor, 'api')->getJson('/api/lf-itse/usuarios')->assertForbidden();
    });

    it('ya no figura como personal sin accesos', function () {
        $auditor = ($this->usuario)(['reportes_completos' => true]);
        $sinNada = ($this->usuario)();

        $ids = array_map(fn ($p) => $p->id, app(RevisionAccesosRepositoryInterface::class)->personalSinAccesos());

        expect($ids)->toContain($sinNada->id)->not->toContain($auditor->id);
    });
});

describe('inspectores', function () {
    it('el módulo solo ve a los usuarios marcados como inspector', function () {
        $itse = ($this->usuario)(['itse' => true]);
        $inspector = ($this->usuario)(['es_inspector' => true], ['first_name' => 'CARLOS', 'is_staff' => false]);
        $otro = ($this->usuario)(['admin' => true]);

        $lista = $this->actingAs($itse, 'api')->getJson('/api/lf-itse/inspectores')->assertOk()->json();
        expect(array_column($lista, 'id'))->toBe([$inspector->id]);

        // Un usuario que no es inspector no existe para este módulo
        $this->actingAs($itse, 'api')->getJson("/api/lf-itse/inspectores/{$otro->id}")->assertNotFound();
    });

    it('solo el jefe de ITSE crea, edita o desactiva inspectores', function () {
        $soloItse = ($this->usuario)(['itse' => true]);
        $jefe = ($this->usuario)(['itse' => true, 'es_jefe_itse' => true]);

        $this->actingAs($soloItse, 'api')
            ->postJson('/api/lf-itse/inspectores', ['username' => 'insp_1', 'password' => 'clave-segura-123'])
            ->assertForbidden();

        $creado = $this->actingAs($jefe, 'api')
            ->postJson('/api/lf-itse/inspectores', ['username' => 'insp_1', 'password' => 'clave-segura-123', 'first_name' => 'ANA'])
            ->assertCreated()
            ->assertJsonPath('perfil.es_inspector', true)
            ->assertJsonPath('perfil.itse', false)
            ->assertJsonPath('is_staff', false)
            ->json();

        $this->actingAs($soloItse, 'api')->putJson("/api/lf-itse/inspectores/{$creado['id']}", ['first_name' => 'X'])->assertForbidden();
        $this->actingAs($jefe, 'api')->putJson("/api/lf-itse/inspectores/{$creado['id']}", ['first_name' => 'ANA MARÍA'])->assertOk();
    });

    it('desactivar no borra la cuenta, y no alcanza a usuarios que no son inspectores', function () {
        $jefe = ($this->usuario)(['itse' => true, 'es_jefe_itse' => true]);
        $inspector = ($this->usuario)(['es_inspector' => true], ['is_staff' => false]);
        $administrador = ($this->usuario)(['admin' => true], ['is_superuser' => true]);

        $this->actingAs($jefe, 'api')->deleteJson("/api/lf-itse/inspectores/{$inspector->id}")->assertNoContent();
        expect(DB::table('auth_user')->where('id', $inspector->id)->value('is_active'))->toBeFalse();

        // Antes, cualquier usuario con acceso ITSE podía borrar a un administrador por esta ruta
        $this->actingAs($jefe, 'api')->deleteJson("/api/lf-itse/inspectores/{$administrador->id}")->assertNotFound();
        $this->actingAs($jefe, 'api')->putJson("/api/lf-itse/inspectores/{$administrador->id}", ['is_active' => false])->assertNotFound();
        expect(DB::table('auth_user')->where('id', $administrador->id)->value('is_active'))->toBeTrue();
    });
});
