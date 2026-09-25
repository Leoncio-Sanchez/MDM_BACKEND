<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    DB::table('auth_user')->insert([
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => 'secret',
    ]);
    $this->user = User::find(1);
});

// ── RENIEC TESTS ─────────────────────────────────────────────────────────────

it('GET /api/lf-itse/reniec/consultar/ sin autenticación devuelve 401', function (): void {
    $response = $this->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(401);
});

it('GET /api/lf-itse/reniec/consultar/ sin parámetro dni devuelve 400', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/');

    $response->assertStatus(400)
        ->assertExactJson(['error' => "El parámetro 'dni' es obligatorio."]);
});

it('GET /api/lf-itse/reniec/consultar/ con dni vacío devuelve 400', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=');

    $response->assertStatus(400)
        ->assertExactJson(['error' => "El parámetro 'dni' es obligatorio."]);
});

it('GET /api/lf-itse/reniec/consultar/ con longitud menor devuelve 400', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=123');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El DNI debe tener exactamente 8 dígitos numéricos.']);
});

it('GET /api/lf-itse/reniec/consultar/ con letras devuelve 400', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=ABCDEFGH');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El DNI debe tener exactamente 8 dígitos numéricos.']);
});

it('GET /api/lf-itse/reniec/consultar/ sin credenciales devuelve 502', function (): void {
    config([
        'services.reniec.dni_usuario' => '',
        'services.reniec.ruc_usuario' => '',
        'services.reniec.password' => '',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(502)
        ->assertExactJson([
            'error' => 'Las credenciales RENIEC no están configuradas (RENIEC_NUDNIUSUARIO, RENIEC_NURUCUSUARIO, RENIEC_PASSWORD).',
        ]);
});

it('GET /api/lf-itse/reniec/consultar/ con timeout devuelve 502', function (): void {
    config([
        'services.reniec.dni_usuario' => 'usuario_test',
        'services.reniec.ruc_usuario' => '20123456789',
        'services.reniec.password' => 'secret_test',
    ]);

    Http::fake([
        'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(502)
        ->assertExactJson(['error' => 'El servicio RENIEC no respondió en el tiempo esperado.']);
});

it('GET /api/lf-itse/reniec/consultar/ con error de conexión devuelve 502', function (): void {
    config([
        'services.reniec.dni_usuario' => 'usuario_test',
        'services.reniec.ruc_usuario' => '20123456789',
        'services.reniec.password' => 'secret_test',
    ]);

    Http::fake([
        'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar*' => fn () => throw new ConnectionException('Could not resolve host'),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(502)
        ->assertExactJson(['error' => 'No se pudo conectar con el servicio RENIEC.']);
});

it('GET /api/lf-itse/reniec/consultar/ con error HTTP 500 upstream devuelve 502', function (): void {
    config([
        'services.reniec.dni_usuario' => 'usuario_test',
        'services.reniec.ruc_usuario' => '20123456789',
        'services.reniec.password' => 'secret_test',
    ]);

    Http::fake([
        'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar*' => Http::response(['error' => 'Error servidor externo'], 500),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(502);
    expect($response->json('error'))->toContain('Error HTTP del servicio RENIEC:');
});

it('GET /api/lf-itse/reniec/consultar/ exitoso devuelve 200 con JSON passthrough', function (): void {
    config([
        'services.reniec.dni_usuario' => 'usuario_test',
        'services.reniec.ruc_usuario' => '20123456789',
        'services.reniec.password' => 'secret_test',
    ]);

    $mockData = [
        'consultarResponse' => [
            'return' => [
                'coResultado' => '0000',
                'datosPersona' => [
                    'prenombres' => 'JUAN CARLOS',
                    'apPrimer' => 'PEREZ',
                    'apSegundo' => 'LOPEZ',
                    'estadoCivil' => 'S',
                    'foto' => '',
                    'ubigeo' => '150101',
                    'direccion' => 'CALLE PRINCIPAL 123',
                ],
            ],
        ],
    ];

    Http::fake([
        'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar*' => Http::response($mockData, 200),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(200)
        ->assertExactJson($mockData);
});

// ── SUNAT TESTS ──────────────────────────────────────────────────────────────

it('GET /api/lf-itse/sunat/consultar/ sin autenticación devuelve 401', function (): void {
    $response = $this->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(401);
});

it('GET /api/lf-itse/sunat/consultar/ sin parámetro ruc devuelve 400', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/');

    $response->assertStatus(400)
        ->assertExactJson(['error' => "El parámetro 'ruc' es obligatorio."]);
});

it('GET /api/lf-itse/sunat/consultar/ con ruc vacío devuelve 400', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=');

    $response->assertStatus(400)
        ->assertExactJson(['error' => "El parámetro 'ruc' es obligatorio."]);
});

it('GET /api/lf-itse/sunat/consultar/ con longitud menor devuelve 400', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=123');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El RUC debe tener exactamente 11 dígitos numéricos.']);
});

it('GET /api/lf-itse/sunat/consultar/ con letras devuelve 400', function (): void {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=2012345678A');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El RUC debe tener exactamente 11 dígitos numéricos.']);
});

it('GET /api/lf-itse/sunat/consultar/ con timeout devuelve 502', function (): void {
    Http::fake([
        'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(502)
        ->assertExactJson(['error' => 'El servicio SUNAT no respondió en el tiempo esperado.']);
});

it('GET /api/lf-itse/sunat/consultar/ con error de conexión devuelve 502', function (): void {
    Http::fake([
        'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales*' => fn () => throw new ConnectionException('Could not resolve host'),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(502)
        ->assertExactJson(['error' => 'No se pudo conectar con el servicio SUNAT.']);
});

it('GET /api/lf-itse/sunat/consultar/ con error HTTP 500 upstream devuelve 502', function (): void {
    Http::fake([
        'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales*' => Http::response(['error' => 'Error servidor SUNAT'], 500),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(502);
    expect($response->json('error'))->toContain('Error HTTP del servicio SUNAT:');
});

it('GET /api/lf-itse/sunat/consultar/ exitoso devuelve 200 con JSON passthrough', function (): void {
    $mockData = [
        'getDatosPrincipalesResponse' => [
            'return' => [
                'ddp_numruc' => '20123456789',
                'ddp_nombre' => 'CORPORACION ABC S.A.C.',
                'ddp_estado' => 'ACTIVO',
                'ddp_condicion' => 'HABIDO',
                'ddp_nomvia' => 'AV. LOS HEROES',
                'ddp_numer1' => '456',
                'ddp_ubigeo' => '150101',
            ],
        ],
    ];

    Http::fake([
        'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales*' => Http::response($mockData, 200),
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(200)
        ->assertExactJson($mockData);
});
