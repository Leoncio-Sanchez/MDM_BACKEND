<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Pide\Domain\Contracts\ReniecClientInterface;
use App\Modules\Pide\Domain\Contracts\SunatClientInterface;
use App\Modules\Pide\Domain\Exceptions\ReniecError;
use App\Modules\Pide\Domain\Exceptions\SunatError;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    DB::table('auth_user')->insert(\Tests\Fixtures\UsuarioAuth::datos([
        'is_staff' => true,
        'is_superuser' => true, // todos los accesos del perfil
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => 'secret',
    ]));
    $this->user = User::find(1);

    $this->reniecMock = Mockery::mock(ReniecClientInterface::class);
    $this->app->instance(ReniecClientInterface::class, $this->reniecMock);

    $this->sunatMock = Mockery::mock(SunatClientInterface::class);
    $this->app->instance(SunatClientInterface::class, $this->sunatMock);
});

afterEach(function () {
    Mockery::close();
});

// ── RENIEC ───────────────────────────────────────────────────────────────────

it('GET /reniec/consultar/ con DNI válido devuelve solo los datos del formulario (sin foto)', function () {
    $this->reniecMock->shouldReceive('consultarPorDni')
        ->once()
        ->with('12345678')
        ->andReturn([
            'consultarResponse' => [
                'return' => [
                    'coResultado' => '0000',
                    'datosPersona' => [
                        'prenombres' => 'JUAN CARLOS',
                        'apPrimer' => 'PEREZ',
                        'apSegundo' => 'LOPEZ',
                        'direccion' => 'JR. LOS PINOS 123',
                        'ubigeo' => 'SAN MARTIN/SAN MARTIN/MORALES',
                        'estadoCivil' => 'SOLTERO',
                        'foto' => 'BASE64DELAFOTO',
                    ],
                ],
            ],
        ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(200)
        ->assertExactJson([
            'dni' => '12345678',
            'nombres' => 'JUAN CARLOS',
            'apellido_paterno' => 'PEREZ',
            'apellido_materno' => 'LOPEZ',
            'direccion' => 'JR. LOS PINOS 123',
            'departamento' => 'SAN MARTIN',
            'provincia' => 'SAN MARTIN',
            'distrito' => 'MORALES',
        ]);
});

it('GET /reniec/consultar/ con DNI que no existe devuelve 404', function () {
    $this->reniecMock->shouldReceive('consultarPorDni')
        ->once()
        ->andReturn(['consultarResponse' => ['return' => ['coResultado' => '0001', 'deResultado' => 'No existe']]]);

    $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678')
        ->assertStatus(404)
        ->assertJsonPath('error', 'No se encontró ese DNI en RENIEC. Verifique el número o registre los datos manualmente.');
});

it('GET /reniec/consultar/ sin dni devuelve 400 con mensaje exacto', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/');

    $response->assertStatus(400)
        ->assertExactJson(['error' => "El parámetro 'dni' es obligatorio."]);
});

it('GET /reniec/consultar/ con dni inválido devuelve 400', function () {
    $this->reniecMock->shouldReceive('consultarPorDni')
        ->once()
        ->with('123')
        ->andThrow(new InvalidArgumentException('El DNI debe tener exactamente 8 dígitos numéricos.'));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=123');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El DNI debe tener exactamente 8 dígitos numéricos.']);
});

it('GET /reniec/consultar/ con ReniecError devuelve 502', function () {
    $this->reniecMock->shouldReceive('consultarPorDni')
        ->once()
        ->with('12345678')
        ->andThrow(ReniecError::timeout());

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(502)
        ->assertExactJson(['error' => 'El servicio RENIEC no respondió en el tiempo esperado.']);
});

it('GET /reniec/consultar/ con error inesperado devuelve 500', function () {
    $this->reniecMock->shouldReceive('consultarPorDni')
        ->once()
        ->with('12345678')
        ->andThrow(new RuntimeException('Error interno no controlado'));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    // El detalle interno no se muestra al usuario
    $response->assertStatus(500)
        ->assertExactJson(['error' => 'Ocurrió un error inesperado al consultar el servicio. Intente nuevamente.']);
});

it('GET /reniec/consultar/ sin autenticación devuelve 401', function () {
    $response = $this->getJson('/api/lf-itse/reniec/consultar/?dni=12345678');

    $response->assertStatus(401);
});

// ── SUNAT ────────────────────────────────────────────────────────────────────

it('GET /sunat/consultar/ con RUC válido devuelve los datos del contribuyente', function () {
    $this->sunatMock->shouldReceive('consultarPorRuc')
        ->once()
        ->with('20123456789')
        ->andReturn([
            'getDatosPrincipalesResponse' => [
                'return' => [
                    'ddp_numruc' => '20123456789',
                    'ddp_nombre' => 'EMPRESA EJEMPLO S.A.C.',
                    'desc_estado' => 'ACTIVO',
                    'desc_flag22' => 'HABIDO',
                    'desc_tipvia' => 'JR.',
                    'ddp_nomvia' => 'LOS ALAMOS',
                    'ddp_numer1' => '450',
                    'ddp_inter1' => '-',
                    'desc_dep' => 'SAN MARTIN',
                    'desc_prov' => 'SAN MARTIN',
                    'desc_dist' => 'MORALES',
                ],
            ],
        ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(200)
        ->assertExactJson([
            'ruc' => '20123456789',
            'razon_social' => 'EMPRESA EJEMPLO S.A.C.',
            'estado' => 'ACTIVO',
            'condicion' => 'HABIDO',
            'direccion' => 'JR. LOS ALAMOS NRO. 450',
            'departamento' => 'SAN MARTIN',
            'provincia' => 'SAN MARTIN',
            'distrito' => 'MORALES',
        ]);
});

it('GET /sunat/consultar/ con RUC que no existe devuelve 404', function () {
    $this->sunatMock->shouldReceive('consultarPorRuc')
        ->once()
        ->andReturn(['getDatosPrincipalesResponse' => ['return' => []]]);

    $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789')
        ->assertStatus(404)
        ->assertJsonPath('error', 'No se encontró ese RUC en SUNAT. Verifique el número o registre los datos manualmente.');
});

it('GET /sunat/consultar/ sin ruc devuelve 400 con mensaje exacto', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/');

    $response->assertStatus(400)
        ->assertExactJson(['error' => "El parámetro 'ruc' es obligatorio."]);
});

it('GET /sunat/consultar/ con ruc inválido devuelve 400', function () {
    $this->sunatMock->shouldReceive('consultarPorRuc')
        ->once()
        ->with('20123')
        ->andThrow(new InvalidArgumentException('El RUC debe tener exactamente 11 dígitos numéricos.'));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123');

    $response->assertStatus(400)
        ->assertExactJson(['error' => 'El RUC debe tener exactamente 11 dígitos numéricos.']);
});

it('GET /sunat/consultar/ con SunatError devuelve 502', function () {
    $this->sunatMock->shouldReceive('consultarPorRuc')
        ->once()
        ->with('20123456789')
        ->andThrow(SunatError::timeout());

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(502)
        ->assertExactJson(['error' => 'El servicio SUNAT no respondió en el tiempo esperado.']);
});

it('GET /sunat/consultar/ con error inesperado devuelve 500', function () {
    $this->sunatMock->shouldReceive('consultarPorRuc')
        ->once()
        ->with('20123456789')
        ->andThrow(new RuntimeException('Error catastrófico en SUNAT'));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(500)
        ->assertExactJson(['error' => 'Ocurrió un error inesperado al consultar el servicio. Intente nuevamente.']);
});

it('GET /sunat/consultar/ sin autenticación devuelve 401', function () {
    $response = $this->getJson('/api/lf-itse/sunat/consultar/?ruc=20123456789');

    $response->assertStatus(401);
});
