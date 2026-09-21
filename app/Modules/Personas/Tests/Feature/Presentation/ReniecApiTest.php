<?php

declare(strict_types=1);
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $userId = DB::table('auth_user')->insertGetId([
        'username' => 'admin_'.uniqid(),
        'password' => 'admin',
        'is_active' => true,
    ]);
    $this->user = User::find($userId);

});

it('consulta reniec', function () {
    Http::fake([
        '*' => Http::response([
            'consultarResponse' => [
                'return' => [
                    'nuDni' => '12345678',
                    'prenombres' => 'Juan',
                    'apePaterno' => 'Perez',
                    'apeMaterno' => 'Gomez',
                ],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($this->user, 'api')->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');
    $response->assertStatus(200)->assertJsonPath('dni', '12345678');
});

it('falla si dni es invalido', function () {
    $response = $this->actingAs($this->user, 'api')->getJson('/api/lf-itse/reniec/consultar/?dni=123');
    $response->assertStatus(422);
});
