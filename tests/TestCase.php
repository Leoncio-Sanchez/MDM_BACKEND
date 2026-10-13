<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Usuario de prueba en `auth_user` (tabla de Django que usa el guard JWT).
     * Se crea una sola vez por test y se reutiliza en llamadas siguientes.
     */
    protected function usuarioApi(): User
    {
        /** @var User|null $existente */
        $existente = User::where('username', 'usuario_api_test')->first();
        if ($existente) {
            return $existente;
        }

        $id = DB::table('auth_user')->insertGetId([
            'username' => 'usuario_api_test',
            'email' => 'usuario_api_test@muni.test',
            'password' => 'secret',
            'is_active' => true,
        ]);

        /** @var User $usuario */
        $usuario = User::find($id);

        return $usuario;
    }

    /** Autentica la siguiente petición contra el guard `api` (rutas protegidas con auth:api). */
    protected function comoUsuarioApi(): static
    {
        return $this->actingAs($this->usuarioApi(), 'api');
    }
}
