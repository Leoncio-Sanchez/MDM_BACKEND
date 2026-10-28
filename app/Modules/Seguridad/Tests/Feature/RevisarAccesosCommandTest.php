<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Feature;

use App\Modules\Seguridad\Domain\ValueObjects\PersonalSinAccesos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class RevisarAccesosCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<string, bool>|null $accesos null = sin fila en usuarios_perfiles */
    private function usuario(string $username, ?array $accesos, array $flags = ['is_staff' => true]): void
    {
        $id = DB::table('auth_user')->insertGetId(\Tests\Fixtures\UsuarioAuth::datos(array_merge(
            ['username' => $username, 'first_name' => 'Nombre', 'last_name' => strtoupper($username)],
            $flags,
        )));

        if ($accesos !== null) {
            DB::table('usuarios_perfiles')->insert(array_merge(
                ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false],
                $accesos,
                ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
            ));
        }
    }

    public function test_lista_al_personal_sin_perfil_o_con_todo_desmarcado(): void
    {
        $this->usuario('sin_perfil', null);
        $this->usuario('todo_desmarcado', []);
        $this->usuario('con_licencias', ['licencias' => true]);                                // tiene acceso
        $this->usuario('superusuario', null, ['is_staff' => true, 'is_superuser' => true]);    // tiene todo
        $this->usuario('inactivo', null, ['is_staff' => true, 'is_active' => false]);          // no puede entrar
        $this->usuario('vecino', null, ['is_staff' => false]);                                 // no es personal

        $this->artisan('usuarios:revisar-accesos')
            ->expectsOutputToContain('2 personas del personal no tienen accesos')
            ->expectsTable(['ID', 'Usuario', 'Nombre', 'Motivo'], [
                [DB::table('auth_user')->where('username', 'sin_perfil')->value('id'), 'sin_perfil', 'Nombre SIN_PERFIL', PersonalSinAccesos::SIN_PERFIL],
                [DB::table('auth_user')->where('username', 'todo_desmarcado')->value('id'), 'todo_desmarcado', 'Nombre TODO_DESMARCADO', PersonalSinAccesos::SIN_ACCESOS],
            ])
            ->expectsOutputToContain('Usuarios y Permisos')
            ->assertSuccessful();
    }

    public function test_avisa_cuando_todo_el_personal_tiene_accesos(): void
    {
        $this->usuario('con_itse', ['itse' => true]);

        $this->artisan('usuarios:revisar-accesos')
            ->expectsOutput('Todo el personal activo tiene al menos un acceso del perfil.')
            ->assertSuccessful();
    }
}
