<?php
declare(strict_types=1);

use App\Models\User;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    DB::table('auth_user')->insert([
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => 'secret',
    ]);
    $this->user = User::find(1);

    $this->repo = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->app->instance(ExpedienteRepositoryInterface::class, $this->repo);

    $this->feriados = Mockery::mock(FeriadoReadRepositoryInterface::class);
    $this->feriados->shouldReceive('esFeriado')->andReturn(false)->byDefault();
    $this->app->instance(FeriadoReadRepositoryInterface::class, $this->feriados);
});

afterEach(function () {
    Mockery::close();
});

it('GET /expedientes/pendientes/ devuelve 200 con array plano', function () {
    $this->repo->shouldReceive('listarPendientes')->once()->andReturn([
        [
            'id' => 1,
            'numero_expediente' => 100,
            'fecha_recepcion' => '2025-01-03T10:00:00Z',
            'nombre' => 'Licencia',
            'fecha_vencimiento' => (new DateTimeImmutable('today'))->modify('+5 days')->format('Y-m-d'),
            'fecha_alerta' => '2025-01-10',
            'persona_nombre' => 'JUAN PEREZ',
            'licencia_pendiente' => true,
            'itse_pendiente' => false,
            'mostrar_alerta' => true,
        ],
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/pendientes/');

    $response->assertStatus(200);
    expect($response->json())->toBeArray()
        ->and($response->json())->toHaveCount(1)
        ->and($response->json()[0]['id'])->toBe(1)
        ->and($response->json()[0]['numero_expediente'])->toBe(100)
        ->and($response->json()[0])->toHaveKey('dias_habiles_restantes');
});

it('GET /expedientes/pendientes/ requiere autenticacion', function () {
    $response = $this->getJson('/api/lf-itse/expedientes/pendientes/');

    $response->assertStatus(401);
});

it('GET /expedientes/buscar/ sin filtro devuelve 400 con mensaje exacto', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/buscar/?valor=100');

    $response->assertStatus(400)
        ->assertJson(['error' => "El parámetro 'filtro' es obligatorio."]);
});

it('GET /expedientes/buscar/ sin valor devuelve 400 con mensaje exacto', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/buscar/?filtro=NUMERO');

    $response->assertStatus(400)
        ->assertJson(['error' => "El parámetro 'valor' es obligatorio."]);
});

it('GET /expedientes/buscar/ con filtro invalido devuelve 400', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/buscar/?filtro=INVENTADO&valor=100');

    $response->assertStatus(400)
        ->assertJson(['error' => "Filtro 'INVENTADO' no válido. Opciones: ID, NUMERO, FECHA_RECEPCION, FECHA_VENCIMIENTO, NOMBRE_SOLICITANTE, RUC_SOLICITANTE"]);
});

it('GET /expedientes/buscar/ con filtro valido devuelve 200 con array plano', function () {
    $this->repo->shouldReceive('buscarPorFiltro')
        ->once()
        ->with('NUMERO', '100')
        ->andReturn([
            [
                'id' => 10,
                'tipo_procedimiento_tupa_id' => 1,
                'numero_expediente' => 100,
                'fecha_recepcion' => '2025-01-03T10:00:00Z',
                'solicitante_id' => 1,
                'representante_id' => null,
                'observaciones' => null,
                'fecha_vencimiento' => (new DateTimeImmutable('today'))->modify('+5 days')->format('Y-m-d'),
                'fecha_alerta' => '2025-01-10',
                'fecha_suspension' => null,
                'dias_ampliacion' => null,
                'motivo_ampliacion' => null,
                'usuario_ampliacion' => null,
                'fecha_digitacion_ampliacion' => null,
                'usuario_id' => 1,
                'fecha_digitacion' => '2025-01-03T10:00:00Z',
                'nombre_procedimiento' => 'LF',
                'plazo_atencion_dias' => 10,
                'dias_alerta_vencimiento' => 3,
                'requiere_itse' => 1,
                'requiere_lf' => 1,
                'solicitante_nombre' => 'JUAN PEREZ',
                'solicitante_ruc' => '20123456789',
                'licencia_pendiente' => true,
                'itse_pendiente' => false,
                'mostrar_alerta' => false,
            ],
        ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/buscar/?filtro=NUMERO&valor=100');

    $response->assertStatus(200);
    expect($response->json())->toBeArray()
        ->and($response->json())->toHaveCount(1)
        ->and($response->json()[0]['id'])->toBe(10)
        ->and($response->json()[0]['numero_expediente'])->toBe(100);
});

it('GET /expedientes/consulta/ sin numero devuelve todos', function () {
    $mockExpediente = Expediente::crear(
        uuid: 'uuid-1',
        numeroExpediente: 501,
        fechaRecepcion: new DateTimeImmutable('2025-01-03 10:00:00'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-01-15'),
        fechaAlerta: new DateTimeImmutable('2025-01-10'),
    );

    $this->repo->shouldReceive('consultar')
        ->once()
        ->with(null)
        ->andReturn([$mockExpediente]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/consulta/');

    $response->assertStatus(200);
    expect($response->json())->toBeArray()
        ->and($response->json())->toHaveCount(1)
        ->and($response->json()[0]['numero_expediente'])->toBe(501);
});

it('GET /expedientes/consulta/ con numero filtra por numero_expediente', function () {
    $mockExpediente = Expediente::crear(
        uuid: 'uuid-2',
        numeroExpediente: 777,
        fechaRecepcion: new DateTimeImmutable('2025-01-03 10:00:00'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-01-15'),
        fechaAlerta: new DateTimeImmutable('2025-01-10'),
    );

    $this->repo->shouldReceive('consultar')
        ->once()
        ->with(777)
        ->andReturn([$mockExpediente]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/consulta/?numero_expediente=777');

    $response->assertStatus(200);
    expect($response->json())->toBeArray()
        ->and($response->json())->toHaveCount(1)
        ->and($response->json()[0]['numero_expediente'])->toBe(777);
});

it('GET /expedientes/buscar/ requiere autenticacion', function () {
    $response = $this->getJson('/api/lf-itse/expedientes/buscar/?filtro=NUMERO&valor=100');

    $response->assertStatus(401);
});

it('GET /expedientes/consulta/ requiere autenticacion', function () {
    $response = $this->getJson('/api/lf-itse/expedientes/consulta/');

    $response->assertStatus(401);
});
