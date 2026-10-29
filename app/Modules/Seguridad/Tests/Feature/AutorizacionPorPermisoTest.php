<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Cada módulo exige el acceso correspondiente del perfil operativo (usuarios_perfiles). */
final class AutorizacionPorPermisoTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<string, bool> $accesos */
    private function personal(array $accesos = [], bool $superusuario = false): User
    {
        $id = DB::table('auth_user')->insertGetId(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'personal_'.uniqid(), 'is_staff' => true, 'is_superuser' => $superusuario,
        ]));

        if ($accesos !== []) {
            DB::table('usuarios_perfiles')->insert(array_merge(
                ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false],
                $accesos,
                ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
            ));
        }

        /** @var User $usuario */
        $usuario = User::find($id);

        return $usuario;
    }

    public function test_solo_con_acceso_admin_no_entra_a_los_modulos_operativos(): void
    {
        $usuario = $this->personal(['admin' => true]);

        $this->actingAs($usuario, 'api')->getJson('/api/lf-itse/licencias-funcionamiento/buscar/?filtro=NOMBRE_COMERCIAL&valor=x')->assertForbidden();
        $this->actingAs($usuario, 'api')->getJson('/api/lf-itse/itse/buscar/?filtro=NOMBRE_COMERCIAL&valor=x')->assertForbidden();
        $this->actingAs($usuario, 'api')->getJson('/api/lf-itse/inspectores/')->assertForbidden();
        $this->actingAs($usuario, 'api')->getJson('/api/lf-itse/expedientes/')->assertForbidden();
        $this->actingAs($usuario, 'api')->getJson('/api/lf-itse/usuarios/')->assertOk();
    }

    public function test_cada_modulo_operativo_exige_su_acceso(): void
    {
        $this->actingAs($this->personal(['licencias' => true]), 'api')
            ->getJson('/api/lf-itse/licencias-funcionamiento/buscar/?filtro=NOMBRE_COMERCIAL&valor=x')->assertOk();
        $this->actingAs($this->personal(['itse' => true]), 'api')
            ->getJson('/api/lf-itse/itse/buscar/?filtro=NOMBRE_COMERCIAL&valor=x')->assertOk();
        $this->actingAs($this->personal(['expedientes' => true]), 'api')
            ->getJson('/api/lf-itse/expedientes/')->assertOk();
    }

    public function test_licencias_puede_leer_expedientes_pero_no_modificarlos(): void
    {
        $usuario = $this->personal(['licencias' => true]);

        $this->actingAs($usuario, 'api')->getJson('/api/lf-itse/expedientes/')->assertOk();
        $this->actingAs($usuario, 'api')->postJson('/api/lf-itse/expedientes/', [])->assertForbidden();
    }

    public function test_los_catalogos_se_leen_sin_admin_pero_solo_admin_los_modifica(): void
    {
        $operador = $this->personal(['licencias' => true]);
        $this->actingAs($operador, 'api')->getJson('/api/lf-itse/giros/')->assertOk();
        $this->actingAs($operador, 'api')
            ->postJson('/api/lf-itse/giros/', ['nombre' => 'X', 'esta_activo' => true])
            ->assertForbidden();
        $this->actingAs($operador, 'api')->getJson('/api/lf-itse/usuarios/')->assertForbidden();
        $this->assertDatabaseCount('giros', 0);
    }

    public function test_sin_perfil_no_tiene_accesos_operativos(): void
    {
        $this->actingAs($this->personal(), 'api')
            ->getJson('/api/lf-itse/licencias-funcionamiento/buscar/?filtro=NOMBRE_COMERCIAL&valor=x')->assertForbidden();
    }

    public function test_el_superusuario_tiene_todos_los_accesos(): void
    {
        $this->actingAs($this->personal(superusuario: true), 'api')
            ->getJson('/api/auth/users/me/')
            ->assertOk()
            ->assertJsonPath('permisos', [
                'expedientes' => true, 'licencias' => true, 'itse' => true, 'admin' => true,
                'inspector' => false, 'jefe_itse' => true, 'reportes' => true,
            ]);
    }

    public function test_me_devuelve_los_accesos_del_perfil(): void
    {
        $this->actingAs($this->personal(['admin' => true]), 'api')
            ->getJson('/api/auth/users/me/')
            ->assertOk()
            ->assertJsonPath('permisos', [
                'expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => true,
                'inspector' => false, 'jefe_itse' => false, 'reportes' => false,
            ]);
    }

    public function test_quitar_un_acceso_aplica_de_inmediato(): void
    {
        $usuario = $this->personal(['licencias' => true]);
        $this->actingAs($usuario, 'api')->getJson('/api/lf-itse/licencias-funcionamiento/buscar/?filtro=NOMBRE_COMERCIAL&valor=x')->assertOk();

        DB::table('usuarios_perfiles')->where('user_id', $usuario->id)->update(['licencias' => false]);

        $this->actingAs($usuario, 'api')->getJson('/api/lf-itse/licencias-funcionamiento/buscar/?filtro=NOMBRE_COMERCIAL&valor=x')->assertForbidden();
    }
}
