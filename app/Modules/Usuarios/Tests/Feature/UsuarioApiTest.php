<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Tests\Feature;

use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioPerfilModel;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class UsuarioApiTest extends TestCase
{
    private string $token;

    private UsuarioModel $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        UsuarioPerfilModel::truncate();
        UsuarioModel::truncate();

        $this->adminUser = UsuarioModel::create(\Tests\Fixtures\UsuarioAuth::datos([
            'id' => 1,
            'username' => 'admin',
            'password' => 'somehash',
            'first_name' => 'Admin',
            'last_name' => 'General',
            'email' => 'admin@muni.gob.pe',
            'is_superuser' => true,
            'is_staff' => true,
            'is_active' => true,
        ]));

        UsuarioPerfilModel::create([
            'user_id' => 1,
            'expedientes' => true,
            'licencias' => true,
            'itse' => true,
            'admin' => true,
            'user_id_digitador' => 1,
            'fecha_digitacion' => now(),
        ]);

        /** @var TokenServiceInterface $tokenService */
        $tokenService = app(TokenServiceInterface::class);
        $authUser = new AuthenticatedUser(
            id: 1,
            username: 'admin',
            email: 'admin@muni.gob.pe',
            role: 'ADMIN_MUNICIPALIDAD',
            ruc: null,
            passwordHash: 'somehash'
        );
        $this->token = $tokenService->issue($authUser)->access;
    }

    public function test_listar_usuarios_devuelve_200_con_array(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/usuarios/');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
        $this->assertCount(1, $response->json());
        $this->assertSame('admin', $response->json()[0]['username']);
        $this->assertSame('Admin General', $response->json()[0]['nombre_completo']);
    }

    public function test_crear_usuario_devuelve_201_y_hashea_password(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/usuarios/', [
                'username' => 'nuevo_usuario',
                'password' => 'clave12345',
                'first_name' => 'Juan',
                'last_name' => 'Perez',
                'email' => 'juan@muni.gob.pe',
                'perfil' => [
                    'expedientes' => true,
                    'licencias' => false,
                    'itse' => false,
                    'admin' => false,
                ],
            ]);

        $response->assertStatus(201);
        $this->assertSame('nuevo_usuario', $response->json('username'));
        $this->assertTrue($response->json('perfil.expedientes'));
        $this->assertFalse($response->json('perfil.licencias'));

        $created = UsuarioModel::where('username', 'nuevo_usuario')->first();
        $this->assertNotNull($created);
        $this->assertStringStartsWith('pbkdf2_sha256$', $created->password);
    }

    public function test_crear_usuario_exige_el_minimo_de_contrasena(): void
    {
        foreach ([[], ['password' => ''], ['password' => '123456789'], ['password' => '          '], ['password' => ['clave123']]] as $clave) {
            $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
                ->postJson('/api/lf-itse/usuarios/', ['username' => 'corta', ...$clave]);

            $response->assertStatus(400);
            $this->assertSame('La contraseña debe tener al menos 10 caracteres.', $response->json('error'));
        }

        $this->assertDatabaseMissing('auth_user', ['username' => 'corta']);
    }

    public function test_obtener_detalle_usuario_devuelve_200(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/usuarios/1/detalle/');

        $response->assertStatus(200);
        $this->assertSame('admin', $response->json('username'));
        $this->assertTrue($response->json('perfil.admin'));
    }

    public function test_actualizar_usuario_modifica_datos_y_perfil(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/usuarios/1/', [
                'first_name' => 'Administrador',
                'last_name' => 'Principal',
                'email' => 'admin_nuevo@muni.gob.pe',
                'perfil' => [
                    'expedientes' => true,
                    'licencias' => true,
                    'itse' => false,
                    'admin' => true,
                ],
            ]);

        $response->assertStatus(200);
        $this->assertSame('Administrador Principal', $response->json('nombre_completo'));
        $this->assertFalse($response->json('perfil.itse'));
    }

    public function test_eliminar_usuario_desactiva_logicamente_con_204(): void
    {
        $target = UsuarioModel::create(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'para_eliminar',
            'password' => 'somehash',
            'is_active' => true,
        ]));

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/usuarios/'.$target->id.'/');

        $response->assertStatus(204);

        $target->refresh();
        $this->assertFalse($target->is_active);
    }

    public function test_cambiar_password_con_pbkdf2(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->patchJson('/api/lf-itse/usuarios/1/cambiar-password/', [
                'password' => 'nuevaPassword123',
                'password_confirm' => 'nuevaPassword123',
            ]);

        $response->assertStatus(200);
        $this->assertSame('Contraseña actualizada correctamente.', $response->json('mensaje'));

        $this->adminUser->refresh();
        $this->assertStringStartsWith('pbkdf2_sha256$', $this->adminUser->password);
    }

    public function test_cambiar_password_valida_longitud_minima(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->patchJson('/api/lf-itse/usuarios/1/cambiar-password/', [
                'password' => '123456789',
                'password_confirm' => '123456789',
            ]);

        $response->assertStatus(400);
        $this->assertSame('La contraseña debe tener al menos 10 caracteres.', $response->json('error'));
    }

    public function test_una_contrasena_heredada_corta_sigue_entrando_y_se_conserva_al_editar(): void
    {
        $hash = app(PasswordHasherInterface::class)->hash('corta1');
        $usuario = UsuarioModel::create(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'heredado', 'password' => $hash, 'is_staff' => true, 'is_active' => true,
        ]));

        $this->postJson('/api/auth/jwt/create/', ['username' => 'heredado', 'password' => 'corta1'])
            ->assertOk()->assertJsonStructure(['access']);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/usuarios/'.$usuario->id.'/', ['first_name' => 'NUEVO'])
            ->assertOk();

        $usuario->refresh();
        $this->assertSame('NUEVO', $usuario->first_name);
        $this->assertSame($hash, $usuario->password);
    }

    public function test_el_minimo_acepta_exactamente_diez_caracteres(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/usuarios/', ['username' => 'diez', 'password' => '1234567890'])
            ->assertStatus(201);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->patchJson('/api/lf-itse/usuarios/1/cambiar-password/', ['password' => '0987654321', 'password_confirm' => '0987654321'])
            ->assertOk();
    }

    public function test_obtener_menus_usuario_devuelve_estructura_completa(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/usuarios/menus/');

        $response->assertStatus(200);
        $menus = $response->json();
        $this->assertIsArray($menus);

        $ids = array_column($menus, 'id');
        $this->assertContains('dashboard', $ids);
        $this->assertContains('expedientes', $ids);
        $this->assertContains('licencias-funcionamiento', $ids);
        $this->assertContains('certificados-itse', $ids);
        $this->assertContains('reportes', $ids);
        $this->assertContains('catalogos', $ids);
        $this->assertContains('usuarios', $ids);
    }
}
