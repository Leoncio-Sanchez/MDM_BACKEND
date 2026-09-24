<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Feature\Presentation;

use App\Modules\Seguridad\Infrastructure\Persistence\Models\UserAuthModel;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AuthParityTest extends TestCase
{
    private string $passwordHash;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('auth_user')) {
            Schema::create('auth_user', function ($table) {
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

        UserAuthModel::truncate();

        $this->passwordHash = 'pbkdf2_sha256$1200000$bCyHs02f8l03epJzsd0N0D$s+PXHa+tN/qJkDe7bnCgcCFghprjFNI2xLALtbqwW3U=';

        UserAuthModel::create([
            'id' => 1,
            'username' => 'admin',
            'password' => $this->passwordHash,
            'email' => 'admin@correo.com',
            'is_superuser' => true,
            'is_staff' => true,
            'is_active' => true,
        ]);
    }

    public function test_login_exitoso_payload_jwt_contiene_los_claims_exactos(): void
    {
        $response = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access', 'refresh']);

        $access = $response->json('access');
        $payload = json_decode(base64_decode(strtr(explode('.', $access)[1], '-_', '+/'), true), true);

        $this->assertSame('access', $payload['token_type']);
        $this->assertSame('1', $payload['user_id']); // STRING
        $this->assertSame('admin', $payload['username']);
        $this->assertSame('ADMIN_MUNICIPALIDAD', $payload['role']);
        $this->assertNull($payload['ruc']);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('jti', $payload);
        $this->assertSame(32, strlen($payload['jti']));
    }

    public function test_login_fallido_devuelve_401_con_mensaje_exacto_de_django(): void
    {
        $response = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)
            ->assertExactJson([
                'detail' => 'La combinación de credenciales no tiene una cuenta activa',
            ]);
    }

    public function test_users_me_devuelve_exactamente_tres_keys(): void
    {
        $loginRes = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $access = $loginRes->json('access');

        $response = $this->withHeader('Authorization', 'Bearer '.$access)
            ->getJson('/api/auth/users/me/');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(3, $data);
        $this->assertSame(['email', 'id', 'username'], array_keys($data));
        $this->assertSame('admin@correo.com', $data['email']);
        $this->assertSame(1, $data['id']);
        $this->assertSame('admin', $data['username']);
    }

    public function test_verify_devuelve_body_vacio_json_object(): void
    {
        $loginRes = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $access = $loginRes->json('access');

        $response = $this->postJson('/api/auth/jwt/verify/', [
            'token' => $access,
        ]);

        $response->assertStatus(200);
        $this->assertSame('{}', $response->getContent());
    }

    public function test_refresh_rota_ambos_tokens(): void
    {
        $loginRes = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $initialAccess = $loginRes->json('access');
        $initialRefresh = $loginRes->json('refresh');

        $response = $this->postJson('/api/auth/jwt/refresh/', [
            'refresh' => $initialRefresh,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access', 'refresh']);

        $newAccess = $response->json('access');
        $newRefresh = $response->json('refresh');

        $this->assertNotSame($initialAccess, $newAccess);
        $this->assertNotSame($initialRefresh, $newRefresh);
    }

    public function test_refresh_con_token_invalido_devuelve_401(): void
    {
        $response = $this->postJson('/api/auth/jwt/refresh/', [
            'refresh' => 'token_completamente_invalido',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['detail']);
    }
}
