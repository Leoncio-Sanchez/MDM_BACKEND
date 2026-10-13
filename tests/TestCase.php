<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Los tests usan PostgreSQL (persistente), no SQLite en memoria: cada test corre dentro de
     * una transacción que se revierte al terminar, para que ninguno deje datos al siguiente.
     */
    use RefreshDatabase;

    /**
     * Usuario administrador de prueba en `auth_user` (tabla de Django que usa el guard JWT).
     * Es staff → rol ADMIN_MUNICIPALIDAD, el que exigen las rutas de gestión.
     * Se crea una sola vez por test y se reutiliza en llamadas siguientes.
     */
    protected function usuarioApi(): User
    {
        /** @var User|null $existente */
        $existente = User::where('username', 'usuario_api_test')->first();
        if ($existente) {
            return $existente;
        }

        $id = DB::table('auth_user')->insertGetId(\Tests\Fixtures\UsuarioAuth::datos([
            'is_staff' => true,
            'is_superuser' => true, // todos los accesos del perfil (usuarios_perfiles)
            'username' => 'usuario_api_test',
            'email' => 'usuario_api_test@muni.test',
            'password' => 'secret',
            'is_active' => true,
        ]));

        /** @var User $usuario */
        $usuario = User::find($id);

        return $usuario;
    }

    /** Autentica la siguiente petición como administrador (rutas con auth:api + rol:ADMIN_MUNICIPALIDAD). */
    protected function comoUsuarioApi(): static
    {
        return $this->actingAs($this->usuarioApi(), 'api');
    }
}
