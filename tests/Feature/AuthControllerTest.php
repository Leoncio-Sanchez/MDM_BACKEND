<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\DjangoJwtTokenService;
use App\Services\DjangoPbkdf2Hasher;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $hasher = new DjangoPbkdf2Hasher;
    $hashedPassword = $hasher->makeHash('secret123');

    DB::table('auth_user')->insert([
        'id' => 99,
        'username' => 'testuser',
        'email' => 'testuser@example.com',
        'password' => $hashedPassword,
    ]);
    $this->user = User::find(99);
});

it('POST /api/auth/jwt/create con credenciales válidas retorna 200 y tokens', function (): void {
    $response = $this->postJson('/api/auth/jwt/create/', [
        'username' => 'testuser',
        'password' => 'secret123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['access', 'refresh']);
});

it('POST /api/auth/jwt/create con credenciales incorrectas retorna 401 y mensaje exacto', function (): void {
    $response = $this->postJson('/api/auth/jwt/create/', [
        'username' => 'testuser',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertExactJson(['detail' => 'La combinación de credenciales no tiene una cuenta activa']);
});

it('POST /api/auth/jwt/create con campos faltantes retorna 400', function (): void {
    $response = $this->postJson('/api/auth/jwt/create/', []);

    $response->assertStatus(400);
});

it('POST /api/auth/jwt/refresh con token válido rota y retorna nuevos tokens', function (): void {
    $tokens = app(DjangoJwtTokenService::class)->issue($this->user);

    $response = $this->postJson('/api/auth/jwt/refresh/', [
        'refresh' => $tokens->refresh,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['access', 'refresh']);
});

it('POST /api/auth/jwt/refresh con token inválido retorna 401', function (): void {
    $response = $this->postJson('/api/auth/jwt/refresh/', [
        'refresh' => 'invalid.refresh.token',
    ]);

    $response->assertStatus(401);
});

it('POST /api/auth/jwt/verify con token válido retorna 200 y objeto vacío', function (): void {
    $tokens = app(DjangoJwtTokenService::class)->issue($this->user);

    $response = $this->postJson('/api/auth/jwt/verify/', [
        'token' => $tokens->access,
    ]);

    $response->assertStatus(200)
        ->assertExactJson([]);
});

it('POST /api/auth/jwt/verify con token inválido retorna 401', function (): void {
    $response = $this->postJson('/api/auth/jwt/verify/', [
        'token' => 'invalid.access.token',
    ]);

    $response->assertStatus(401)
        ->assertExactJson(['detail' => 'Token is invalid or expired']);
});

it('GET /api/auth/users/me con autenticación retorna datos de usuario', function (): void {
    $tokens = app(DjangoJwtTokenService::class)->issue($this->user);

    $response = $this->withHeader('Authorization', 'Bearer '.$tokens->access)
        ->getJson('/api/auth/users/me/');

    $response->assertStatus(200)
        ->assertExactJson([
            'email' => 'testuser@example.com',
            'id' => 99,
            'username' => 'testuser',
        ]);
});

it('GET /api/auth/users/me sin token retorna 401', function (): void {
    $response = $this->getJson('/api/auth/users/me/');

    $response->assertStatus(401);
});
