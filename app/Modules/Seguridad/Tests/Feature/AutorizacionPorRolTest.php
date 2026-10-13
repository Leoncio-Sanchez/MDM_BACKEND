<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Las rutas de gestión (rol:ADMIN_MUNICIPALIDAD) rechazan a ciudadanos e inspectores. */
final class AutorizacionPorRolTest extends TestCase
{
    use RefreshDatabase;

    private const RUTA_GESTION = '/api/lf-itse/giros';

    private function crearUsuario(string $username, bool $esStaff = false, bool $esInspector = false): User
    {
        $id = DB::table('auth_user')->insertGetId([
            'username' => $username, 'password' => 'x', 'is_active' => true, 'is_staff' => $esStaff,
        ]);
        if ($esInspector) {
            DB::table('usuarios_perfiles')->insert(['user_id' => $id, 'es_inspector' => true]);
        }

        /** @var User $usuario */
        $usuario = User::find($id);

        return $usuario;
    }

    public function test_el_administrador_accede_a_la_gestion(): void
    {
        $this->actingAs($this->crearUsuario('admin_t', esStaff: true), 'api')
            ->getJson(self::RUTA_GESTION)
            ->assertOk();
    }

    public function test_un_ciudadano_no_accede_a_la_gestion(): void
    {
        $this->actingAs($this->crearUsuario('ciudadano_t'), 'api')
            ->getJson(self::RUTA_GESTION)
            ->assertForbidden()
            ->assertJsonPath('error', 'No tiene permisos para realizar esta operación.');
    }

    public function test_un_inspector_no_accede_a_la_gestion(): void
    {
        $this->actingAs($this->crearUsuario('inspector_t', esInspector: true), 'api')
            ->postJson(self::RUTA_GESTION, ['nombre' => 'X', 'esta_activo' => true])
            ->assertForbidden();

        $this->assertDatabaseCount('giros', 0);
    }

    public function test_quitar_el_rol_aplica_de_inmediato(): void
    {
        $usuario = $this->crearUsuario('ex_admin', esStaff: true);
        $this->actingAs($usuario, 'api')->getJson(self::RUTA_GESTION)->assertOk();

        DB::table('auth_user')->where('id', $usuario->id)->update(['is_staff' => false]);

        $this->actingAs($usuario, 'api')->getJson(self::RUTA_GESTION)->assertForbidden();
    }

    public function test_cualquier_usuario_autenticado_ve_su_propio_perfil(): void
    {
        $this->actingAs($this->crearUsuario('ciudadano_me'), 'api')
            ->getJson('/api/auth/users/me/')
            ->assertOk();
    }
}
