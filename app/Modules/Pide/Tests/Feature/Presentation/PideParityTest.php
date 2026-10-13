<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Pide\Domain\Contracts\ReniecClientInterface;
use App\Modules\Pide\Domain\Contracts\SunatClientInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    DB::table('auth_user')->insert(\Tests\Fixtures\UsuarioAuth::datos([
        'is_staff' => true,
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => 'secret',
    ]));
    $this->user = User::find(1);
});

// ── RENIEC ERROR PATHS (PARIDAD CON DJANGO) ──────────────────────────────────

it('reniec: DNI inválido por longitud devuelve 400 con mensaje exacto de Django', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=123');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El DNI debe tener exactamente 8 dígitos numéricos.']);
});

it('reniec: DNI con letras devuelve 400 con mensaje exacto de Django', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=ABCDEFGH');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El DNI debe tener exactamente 8 dígitos numéricos.']);
});

it('reniec: sin parámetro devuelve 400 con mensaje exacto de Django', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/');

    $response->assertStatus(400)
        ->assertExactJson(['error' => "El parámetro 'dni' es obligatorio."]);
});

it('reniec: credenciales no configuradas devuelve 502 con mensaje exacto de Django', function () {
    config([
        'services.reniec.dni_usuario' => '',
        'services.reniec.ruc_usuario' => '',
        'services.reniec.password' => '',
    ]);

    $this->app->forgetInstance(ReniecClientInterface::class);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(502)
        ->assertExactJson([
            'error' => 'Las credenciales RENIEC no están configuradas '
                .'(RENIEC_NUDNIUSUARIO, RENIEC_NURUCUSUARIO, RENIEC_PASSWORD).',
        ]);
});

it('reniec: timeout upstream mapea a 502 con mensaje exacto de Django', function () {
    config([
        'services.reniec.dni_usuario' => 'user',
        'services.reniec.ruc_usuario' => 'ruc',
        'services.reniec.password' => 'pass',
    ]);
    $this->app->forgetInstance(ReniecClientInterface::class);

    Http::fake([
        'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(502)
        ->assertExactJson(['error' => 'El servicio RENIEC no respondió en el tiempo esperado.']);
});

// ── SUNAT ERROR PATHS (PARIDAD CON DJANGO) ───────────────────────────────────

it('sunat: RUC inválido por longitud devuelve 400 con mensaje exacto de Django', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=123');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El RUC debe tener exactamente 11 dígitos numéricos.']);
});

it('sunat: RUC con letras devuelve 400 con mensaje exacto de Django', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=2012345678A');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El RUC debe tener exactamente 11 dígitos numéricos.']);
});

it('sunat: sin parámetro devuelve 400 con mensaje exacto de Django', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/');

    $response->assertStatus(400)
        ->assertExactJson(['error' => "El parámetro 'ruc' es obligatorio."]);
});

it('sunat: timeout upstream mapea a 502 con mensaje exacto de Django', function () {
    $this->app->forgetInstance(SunatClientInterface::class);

    Http::fake([
        'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(502)
        ->assertExactJson(['error' => 'El servicio SUNAT no respondió en el tiempo esperado.']);
});
