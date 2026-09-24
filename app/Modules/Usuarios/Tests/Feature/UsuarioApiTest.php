<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Tests\Feature;

use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioPerfilModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class UsuarioApiTest extends TestCase
{
    private string $token;

    private UsuarioModel $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('auth_user')) {
            Schema::create('auth_user', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('password');
                $table->string('first_name')->default('');
                $table->string('last_name')->default('');
                $table->string('email')->default('');
                $table->boolean('is_superuser')->default(false);
                $table->boolean('is_staff')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login')->nullable();
                $table->timestamp('date_joined')->nullable();
            });
        }

        if (! Schema::hasTable('usuarios_perfiles')) {
            Schema::create('usuarios_perfiles', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->boolean('expedientes')->default(false);
                $table->boolean('licencias')->default(false);
                $table->boolean('itse')->default(false);
                $table->boolean('admin')->default(false);
                $table->integer('user_id_digitador')->default(1);
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        UsuarioPerfilModel::truncate();
        UsuarioModel::truncate();

        $this->adminUser = UsuarioModel::create([
            'id' => 1,
            'username' => 'admin',
            'password' => 'somehash',
            'first_name' => 'Admin',
            'last_name' => 'General',
            'email' => 'admin@muni.gob.pe',
            'is_superuser' => true,
            'is_staff' => true,
            'is_active' => true,
        ]);

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
                'password' => 'clave123',
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
        $target = UsuarioModel::create([
            'username' => 'para_eliminar',
            'password' => 'somehash',
            'is_active' => true,
        ]);

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
                'password' => '123',
                'password_confirm' => '123',
            ]);

        $response->assertStatus(400);
        $this->assertSame('La contraseña debe tener al menos 6 caracteres.', $response->json('error'));
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
