<?php
declare(strict_types=1);

use App\Models\User;
use App\Modules\ITSE\Domain\Contracts\ItseGiroRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseEstadoRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    DB::table('auth_user')->insert([
        'is_staff' => true,
        'id' => 1,
        'username' => 'test',
        'email' => 'test@test.com',
        'password' => 'secret',
    ]);
    $this->user = User::find(1);

    $this->mockItse = new Itse(
        id: 1,
        uuid: Uuid::uuid4()->toString(),
        numeroItse: 999999,
        fechaExpedicion: CarbonImmutable::parse('2026-01-01'),
        fechaSolicitudRenovacion: CarbonImmutable::parse('2026-02-01'),
        fechaCaducidad: CarbonImmutable::parse('2027-01-01'),
        nombreComercial: 'Test',
        direccion: 'Dir',
        resolucionNumero: 'RES-1',
        area: 50.0,
        capacidadAforo: 50,
        numeroReciboPago: 'REC-1',
        observaciones: null,
        sePuedePublicar: true,
        fechaNotificacion: null,
        fechaDigitacion: CarbonImmutable::parse('2026-01-01 10:00:00'),
        tipoItseId: 1,
        expedienteId: 1,
        itsePrincipalId: null,
        usuarioId: 1,
        nivelRiesgoId: 1,
        conductorId: 1,
        titularId: 1,
    );
});

// ── 401 Unauthenticated Tests ────────────────────────────────────────────────

it('retorna 401 en /giros/ sin token', function () {
    $this->getJson('/api/lf-itse/itse/1/giros/')->assertStatus(401);
});

it('retorna 401 en /estados/ sin token', function () {
    $this->getJson('/api/lf-itse/itse/1/estados/')->assertStatus(401);
});

it('retorna 401 en /inspectores/ sin token', function () {
    $this->getJson('/api/lf-itse/itse/1/inspectores/')->assertStatus(401);
});

it('retorna 401 en /por-renovar/ sin token', function () {
    $this->getJson('/api/lf-itse/itse/por-renovar/')->assertStatus(401);
});

it('retorna 401 en /consulta/ sin token', function () {
    $this->getJson('/api/lf-itse/itse/consulta/')->assertStatus(401);
});

// ── 404 Not Found Tests ──────────────────────────────────────────────────────

it('retorna 404 en /giros/ si itse no existe', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existePorId')->with(999)->andReturn(false);
    });

    $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/itse/999/giros/')
        ->assertStatus(404);
});

it('retorna 404 en /estados/ si itse no existe', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existePorId')->with(999)->andReturn(false);
    });

    $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/itse/999/estados/')
        ->assertStatus(404);
});

// ── Success Tests ────────────────────────────────────────────────────────────

it('retorna 200 y lista giros asociados', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existePorId')->with(1)->andReturn(true);
    });
    $this->mock(ItseGiroRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('listarPorItse')->with(1)->andReturn([
            ['id' => 1, 'itse_id' => 1, 'giro_id' => 10, 'ciiu_id' => 111, 'nombre' => 'GIRO 1'],
        ]);
    });

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/itse/1/giros/')
        ->assertStatus(200);

    $this->assertCount(1, $response->json());
    $this->assertSame('GIRO 1', $response->json()[0]['nombre']);
});

it('retorna 200 y lista estados asociados', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existePorId')->with(1)->andReturn(true);
    });
    $this->mock(ItseEstadoRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('listarPorItse')->with(1)->andReturn([
            ['id' => 1, 'itse_id' => 1, 'estado_nombre' => 'Vigente', 'es_para_itse' => true],
        ]);
    });

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/itse/1/estados/')
        ->assertStatus(200);

    $this->assertCount(1, $response->json());
    $this->assertSame('Vigente', $response->json()[0]['estado_nombre']);
});

it('retorna 200 y array vacio en /inspectores/ por TD-010', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/itse/1/inspectores/')
        ->assertStatus(200);

    $this->assertSame([], $response->json());
});

it('retorna 200 y lista ITSEs por renovar', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('listarPorRenovar')
            ->once()
            ->andReturn([$this->mockItse]);
    });

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/itse/por-renovar/?fecha_desde=2026-01-01')
        ->assertStatus(200);

    $this->assertCount(1, $response->json());
    $this->assertSame(999999, $response->json()[0]['numero_itse']);
});

it('retorna 200 y consulta ITSE por numero', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('consultaRapida')
            ->once()
            ->with(999999)
            ->andReturn([$this->mockItse]);
    });

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/itse/consulta/?numero_itse=999999')
        ->assertStatus(200);

    $this->assertCount(1, $response->json());
    $this->assertSame(999999, $response->json()[0]['numero_itse']);
});
