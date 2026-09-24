<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Feature\Presentation;

use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Seguridad\Infrastructure\Jwt\DjangoJwtTokenService;
use App\Modules\Seguridad\Infrastructure\Persistence\Models\UserAuthModel;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class JwtAuthControllerTest extends TestCase
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

        // Hash real de Django para "admin"
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

    public function test_login_con_credenciales_validas_devuelve_200_con_access_y_refresh(): void
    {
        $response = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access', 'refresh']);

        $access = $response->json('access');
        $refresh = $response->json('refresh');

        $this->assertIsString($access);
        $this->assertIsString($refresh);
        $this->assertNotEmpty($access);
        $this->assertNotEmpty($refresh);
    }

    public function test_login_con_password_incorrecta_devuelve_401_con_mensaje_exacto_de_django(): void
    {
        $response = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertExactJson([
                'detail' => 'La combinación de credenciales no tiene una cuenta activa',
            ]);
    }

    public function test_login_con_usuario_inexistente_devuelve_401_con_mensaje_exacto(): void
    {
        $response = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'no_existe',
            'password' => 'cualquiera',
        ]);

        $response->assertStatus(401)
            ->assertExactJson([
                'detail' => 'La combinación de credenciales no tiene una cuenta activa',
            ]);
    }

    public function test_login_sin_username_devuelve_400(): void
    {
        $response = $this->postJson('/api/auth/jwt/create/', [
            'password' => 'admin',
        ]);

        $response->assertStatus(400);
    }

    public function test_login_sin_password_devuelve_400(): void
    {
        $response = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
        ]);

        $response->assertStatus(400);
    }

    public function test_refresh_devuelve_nuevo_token_pair(): void
    {
        $loginRes = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $refreshToken = $loginRes->json('refresh');

        $refreshRes = $this->postJson('/api/auth/jwt/refresh/', [
            'refresh' => $refreshToken,
        ]);

        $refreshRes->assertStatus(200)
            ->assertJsonStructure(['access', 'refresh']);

        $this->assertNotSame($loginRes->json('access'), $refreshRes->json('access'));
        $this->assertNotSame($loginRes->json('refresh'), $refreshRes->json('refresh'));
    }

    public function test_refresh_con_token_invalido_devuelve_401(): void
    {
        $response = $this->postJson('/api/auth/jwt/refresh/', [
            'refresh' => 'token.invalido.123',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['detail']);
    }

    public function test_refresh_sin_parametro_devuelve_400(): void
    {
        $response = $this->postJson('/api/auth/jwt/refresh/', []);

        $response->assertStatus(400);
    }

    public function test_verify_con_token_valido_devuelve_200_con_cuerpo_vacio(): void
    {
        $loginRes = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $accessToken = $loginRes->json('access');

        $verifyRes = $this->postJson('/api/auth/jwt/verify/', [
            'token' => $accessToken,
        ]);

        $verifyRes->assertStatus(200)
            ->assertExactJson([]);
    }

    public function test_verify_con_token_invalido_devuelve_401(): void
    {
        $response = $this->postJson('/api/auth/jwt/verify/', [
            'token' => 'token.falso.invalido',
        ]);

        $response->assertStatus(401)
            ->assertExactJson(['detail' => 'Token is invalid or expired']);
    }

    public function test_verify_sin_token_devuelve_400(): void
    {
        $response = $this->postJson('/api/auth/jwt/verify/', []);

        $response->assertStatus(400);
    }

    public function test_get_users_me_sin_token_devuelve_401(): void
    {
        $response = $this->getJson('/api/auth/users/me/');

        $response->assertStatus(401);
    }

    public function test_get_users_me_con_token_valido_devuelve_exactamente_las_tres_keys(): void
    {
        $loginRes = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $accessToken = $loginRes->json('access');

        $meRes = $this->withHeader('Authorization', 'Bearer '.$accessToken)
            ->getJson('/api/auth/users/me/');

        $meRes->assertStatus(200);

        $data = $meRes->json();
        $this->assertCount(3, $data);
        $this->assertSame([
            'email' => 'admin@correo.com',
            'id' => 1,
            'username' => 'admin',
        ], $data);
    }

    public function test_get_users_me_con_token_expirado_devuelve_401(): void
    {
        $tokenService = app(TokenServiceInterface::class);
        $user = new AuthenticatedUser(
            1, 'admin', 'admin@correo.com', 'ADMIN_MUNICIPALIDAD', null, ''
        );

        // Crear servicio con TTL negativo para generar token expirado
        $expiredService = new DjangoJwtTokenService(
            secret: (string) config('jwt.secret'),
            accessTtlSeconds: -60,
            refreshTtlSeconds: -60,
        );

        $pair = $expiredService->issue($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$pair->access)
            ->getJson('/api/auth/users/me/');

        $response->assertStatus(401);
    }

    public function test_token_access_generado_contiene_claims_compatibles_con_frontend_vue(): void
    {
        $loginRes = $this->postJson('/api/auth/jwt/create/', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $accessToken = $loginRes->json('access');
        $payloadRaw = explode('.', $accessToken)[1];
        $payload = json_decode(base64_decode(strtr($payloadRaw, '-_', '+/'), true), true);

        $this->assertSame('access', $payload['token_type']);
        $this->assertSame('1', $payload['user_id']); // STRING
        $this->assertSame('admin', $payload['username']);
        $this->assertSame('ADMIN_MUNICIPALIDAD', $payload['role']);
        $this->assertNull($payload['ruc']);
        $this->assertSame(32, strlen($payload['jti']));
    }
}
