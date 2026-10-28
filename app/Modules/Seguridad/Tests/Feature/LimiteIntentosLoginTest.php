<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Feature;

use App\Modules\Seguridad\Infrastructure\Hashing\DjangoPbkdf2Hasher;
use App\Modules\Seguridad\Infrastructure\Providers\SeguridadServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** El login limita los intentos para frenar el robo de contraseñas por fuerza bruta. */
final class LimiteIntentosLoginTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/auth/jwt/create/';

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('auth_user')->insert(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'operador', 'password' => (new DjangoPbkdf2Hasher())->hash('clave-correcta'), 'is_staff' => true,
        ]));
    }

    public function test_bloquea_al_superar_los_intentos_de_un_usuario(): void
    {
        for ($i = 0; $i < SeguridadServiceProvider::INTENTOS_POR_USUARIO; $i++) {
            $this->postJson(self::URL, ['username' => 'operador', 'password' => 'mala'])->assertUnauthorized();
        }

        $this->postJson(self::URL, ['username' => 'operador', 'password' => 'mala'])
            ->assertStatus(429)
            ->assertJsonPath('detail', 'Demasiados intentos de inicio de sesión. Espere un minuto e intente nuevamente.');
    }

    public function test_bloqueado_no_entra_ni_con_la_clave_correcta(): void
    {
        for ($i = 0; $i < SeguridadServiceProvider::INTENTOS_POR_USUARIO; $i++) {
            $this->postJson(self::URL, ['username' => 'operador', 'password' => 'mala']);
        }

        $this->postJson(self::URL, ['username' => 'operador', 'password' => 'clave-correcta'])->assertStatus(429);
    }

    public function test_el_bloqueo_de_un_usuario_no_afecta_a_otro(): void
    {
        for ($i = 0; $i < SeguridadServiceProvider::INTENTOS_POR_USUARIO + 1; $i++) {
            $this->postJson(self::URL, ['username' => 'otro', 'password' => 'mala']);
        }

        $this->postJson(self::URL, ['username' => 'operador', 'password' => 'clave-correcta'])
            ->assertOk()
            ->assertJsonStructure(['access', 'refresh']);
    }

    public function test_limita_a_quien_prueba_muchos_usuarios_desde_la_misma_ip(): void
    {
        for ($i = 0; $i < SeguridadServiceProvider::INTENTOS_POR_IP; $i++) {
            $this->postJson(self::URL, ['username' => "usuario{$i}", 'password' => 'mala'])->assertUnauthorized();
        }

        $this->postJson(self::URL, ['username' => 'usuario_nuevo', 'password' => 'mala'])->assertStatus(429);
    }
}
