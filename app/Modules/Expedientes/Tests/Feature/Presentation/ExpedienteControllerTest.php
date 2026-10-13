<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Expedientes\Domain\Contracts\AutorizacionImprocedenteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\TipoProcedimientoTupaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function crearExpedienteFeatureFixture(int $id = 10, int $tipoTupaId = 1): Expediente
{
    return new Expediente(
        id: $id,
        uuid: 'uuid-'.$id,
        numeroExpediente: 100 + $id,
        fechaRecepcion: new DateTimeImmutable('2026-09-01 10:00:00'),
        tipoProcedimientoTupaId: $tipoTupaId,
        solicitanteId: 1,
        representanteId: null,
        observaciones: null,
        fechaVencimiento: new DateTimeImmutable('2026-09-15'),
        fechaAlerta: new DateTimeImmutable('2026-09-12'),
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2026-09-01 10:00:00'),
        autorizacionesImprocedentes: [],
    );
}

function crearAutorizacionFeatureFixture(int $id = 1, int $expedienteId = 10, TipoAutorizacion $tipo = TipoAutorizacion::LF): AutorizacionImprocedente
{
    return new AutorizacionImprocedente(
        id: $id,
        expedienteId: $expedienteId,
        tipoAutorizacion: $tipo,
        fechaRechazo: new DateTimeImmutable('2026-09-15'),
        documento: 'RES-001',
        observaciones: 'Observaciones de prueba',
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2026-09-15 12:00:00'),
    );
}

beforeEach(function () {
    DB::table('auth_user')->insert(\Tests\Fixtures\UsuarioAuth::datos([
        'is_staff' => true,
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => 'secret',
    ]));
    $this->user = User::find(1);

    $this->repo = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->app->instance(ExpedienteRepositoryInterface::class, $this->repo);

    $this->autorizacionesRepo = Mockery::mock(AutorizacionImprocedenteRepositoryInterface::class);
    $this->app->instance(AutorizacionImprocedenteRepositoryInterface::class, $this->autorizacionesRepo);

    $this->tiposTupa = Mockery::mock(TipoProcedimientoTupaReadRepositoryInterface::class);
    $this->app->instance(TipoProcedimientoTupaReadRepositoryInterface::class, $this->tiposTupa);

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

// ── 7C Workflows Tests ───────────────────────────────────────────────────

it('GET /expedientes/{pk}/autorizacion-improcedente/ requiere autenticacion', function () {
    $response = $this->getJson('/api/lf-itse/expedientes/10/autorizacion-improcedente/?tipo=LF');

    $response->assertStatus(401);
});

it('GET /expedientes/{pk}/autorizacion-improcedente/ sin tipo devuelve 400 con mensaje exacto', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/10/autorizacion-improcedente/');

    $response->assertStatus(400)
        ->assertJson(['error' => "El parámetro 'tipo' es requerido ('LF' o 'ITSE')."]);
});

it('GET /expedientes/{pk}/autorizacion-improcedente/ con tipo invalido devuelve 400', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/10/autorizacion-improcedente/?tipo=XX');

    $response->assertStatus(400)
        ->assertJson(['error' => "Tipo de autorización 'XX' no válido. Opciones: LF, ITSE"]);
});

it('GET /expedientes/{pk}/autorizacion-improcedente/ sin registro devuelve 200 con null', function () {
    $this->autorizacionesRepo->shouldReceive('buscarPorExpedienteYTipo')
        ->with(10, TipoAutorizacion::LF)
        ->once()
        ->andReturnNull();

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/10/autorizacion-improcedente/?tipo=LF');

    $response->assertStatus(200);
    expect($response->getContent())->toBe('');
});

it('GET /expedientes/{pk}/autorizacion-improcedente/ con registro devuelve 200 con resource', function () {
    $auto = crearAutorizacionFeatureFixture(1, 10, TipoAutorizacion::LF);
    $this->autorizacionesRepo->shouldReceive('buscarPorExpedienteYTipo')
        ->with(10, TipoAutorizacion::LF)
        ->once()
        ->andReturn($auto);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/10/autorizacion-improcedente/?tipo=LF');

    $response->assertStatus(200);
    expect($response->json())->toBeArray()
        ->and($response->json()['id'])->toBe(1)
        ->and($response->json()['tipo_autorizacion'])->toBe('LF')
        ->and($response->json()['fecha_rechazo'])->toBe('2026-09-15')
        ->and($response->json()['documento'])->toBe('RES-001')
        ->and($response->json()['observaciones'])->toBe('Observaciones de prueba')
        ->and($response->json()['expediente'])->toBe(10)
        ->and($response->json()['usuario'])->toBe(1);
});

it('POST /expedientes/{pk}/ampliacion-plazo/ requiere autenticacion', function () {
    $response = $this->postJson('/api/lf-itse/expedientes/10/ampliacion-plazo/', []);

    $response->assertStatus(401);
});

it('POST /expedientes/{pk}/ampliacion-plazo/ sin campos obligatorios devuelve 400', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/ampliacion-plazo/', []);

    $response->assertStatus(400);
});

it('POST /expedientes/{pk}/ampliacion-plazo/ con dias_ampliacion=0 devuelve 400', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/ampliacion-plazo/', [
            'fecha_suspension' => '2026-09-10',
            'dias_ampliacion' => 0,
            'motivo_ampliacion' => 'Subsanación',
        ]);

    $response->assertStatus(400);
});

it('POST /expedientes/{pk}/ampliacion-plazo/ con expediente inexistente devuelve 404', function () {
    $this->repo->shouldReceive('buscarPorId')
        ->with(999)
        ->once()
        ->andReturnNull();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/999/ampliacion-plazo/', [
            'fecha_suspension' => '2026-09-10',
            'dias_ampliacion' => 5,
            'motivo_ampliacion' => 'Subsanación',
        ]);

    $response->assertStatus(404);
});

it('POST /expedientes/{pk}/ampliacion-plazo/ con datos validos devuelve 200 con resource de expediente', function () {
    $exp = crearExpedienteFeatureFixture(10, 1);
    $this->repo->shouldReceive('buscarPorId')->with(10)->once()->andReturn($exp);
    $this->tiposTupa->shouldReceive('buscarPorId')->with(1)->once()->andReturn((object) ['id' => 1, 'dias_alerta_vencimiento' => 2]);
    $this->repo->shouldReceive('actualizar')->once()->andReturnArg(0);

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/ampliacion-plazo/', [
            'fecha_suspension' => '2026-09-10',
            'dias_ampliacion' => 5,
            'motivo_ampliacion' => 'Subsanación de observaciones',
        ]);

    $response->assertStatus(200);
    expect($response->json()['id'])->toBe(10)
        ->and($response->json()['dias_ampliacion'])->toBe(5)
        ->and($response->json()['motivo_ampliacion'])->toBe('Subsanación de observaciones');
});

it('POST /expedientes/{pk}/denegar-licencia/ requiere autenticacion', function () {
    $response = $this->postJson('/api/lf-itse/expedientes/10/denegar-licencia/', []);

    $response->assertStatus(401);
});

it('POST /expedientes/{pk}/denegar-licencia/ sin campos obligatorios devuelve 400', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/denegar-licencia/', []);

    $response->assertStatus(400);
});

it('POST /expedientes/{pk}/denegar-licencia/ con documento vacio devuelve 400', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/denegar-licencia/', [
            'fecha_rechazo' => '2026-09-20',
            'documento' => '',
            'observaciones' => 'Test',
        ]);

    $response->assertStatus(400);
});

it('POST /expedientes/{pk}/denegar-licencia/ con expediente inexistente devuelve 404', function () {
    $this->repo->shouldReceive('buscarPorId')
        ->with(999)
        ->once()
        ->andReturnNull();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/999/denegar-licencia/', [
            'fecha_rechazo' => '2026-09-20',
            'documento' => 'DOC-01',
            'observaciones' => 'Test',
        ]);

    $response->assertStatus(404);
});

it('POST /expedientes/{pk}/denegar-licencia/ si ya tiene licencia devuelve 400 con mensaje exacto', function () {
    $exp = crearExpedienteFeatureFixture(10);
    $this->repo->shouldReceive('buscarPorId')->with(10)->once()->andReturn($exp);
    $this->repo->shouldReceive('tieneLicencia')->with(10)->once()->andReturnTrue();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/denegar-licencia/', [
            'fecha_rechazo' => '2026-09-20',
            'documento' => 'DOC-01',
            'observaciones' => 'Test',
        ]);

    $response->assertStatus(400)
        ->assertJson(['error' => 'El expediente 10 ya tiene una licencia de funcionamiento emitida. Para denegarla primero debe eliminar la licencia.']);
});

it('POST /expedientes/{pk}/denegar-licencia/ con datos validos devuelve 201 con autorizacion creada', function () {
    $exp = crearExpedienteFeatureFixture(10);
    $this->repo->shouldReceive('buscarPorId')->with(10)->once()->andReturn($exp);
    $this->repo->shouldReceive('tieneLicencia')->with(10)->once()->andReturnFalse();
    $auto = crearAutorizacionFeatureFixture(1, 10, TipoAutorizacion::LF);
    $this->autorizacionesRepo->shouldReceive('crear')->once()->andReturn($auto);

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/denegar-licencia/', [
            'fecha_rechazo' => '2026-09-15',
            'documento' => 'RES-001',
            'observaciones' => 'Observaciones de prueba',
        ]);

    $response->assertStatus(201);
    expect($response->json()['id'])->toBe(1)
        ->and($response->json()['tipo_autorizacion'])->toBe('LF')
        ->and($response->json()['expediente'])->toBe(10);
});

it('POST /expedientes/{pk}/denegar-itse/ requiere autenticacion', function () {
    $response = $this->postJson('/api/lf-itse/expedientes/10/denegar-itse/', []);

    $response->assertStatus(401);
});

it('POST /expedientes/{pk}/denegar-itse/ sin campos obligatorios devuelve 400', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/denegar-itse/', []);

    $response->assertStatus(400);
});

it('POST /expedientes/{pk}/denegar-itse/ con expediente inexistente devuelve 404', function () {
    $this->repo->shouldReceive('buscarPorId')
        ->with(999)
        ->once()
        ->andReturnNull();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/999/denegar-itse/', [
            'fecha_rechazo' => '2026-09-20',
            'documento' => 'DOC-01',
            'observaciones' => 'Test',
        ]);

    $response->assertStatus(404);
});

it('POST /expedientes/{pk}/denegar-itse/ si ya tiene ITSE devuelve 400 con mensaje exacto', function () {
    $exp = crearExpedienteFeatureFixture(10);
    $this->repo->shouldReceive('buscarPorId')->with(10)->once()->andReturn($exp);
    $this->repo->shouldReceive('tieneItse')->with(10)->once()->andReturnTrue();

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/denegar-itse/', [
            'fecha_rechazo' => '2026-09-20',
            'documento' => 'DOC-01',
            'observaciones' => 'Test',
        ]);

    $response->assertStatus(400)
        ->assertJson(['error' => 'El expediente 10 ya tiene una ITSE emitida. Para declararla desfavorable primero debe eliminar la ITSE.']);
});

it('POST /expedientes/{pk}/denegar-itse/ con datos validos devuelve 201 con autorizacion creada', function () {
    $exp = crearExpedienteFeatureFixture(10);
    $this->repo->shouldReceive('buscarPorId')->with(10)->once()->andReturn($exp);
    $this->repo->shouldReceive('tieneItse')->with(10)->once()->andReturnFalse();
    $auto = crearAutorizacionFeatureFixture(2, 10, TipoAutorizacion::ITSE);
    $this->autorizacionesRepo->shouldReceive('crear')->once()->andReturn($auto);

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/10/denegar-itse/', [
            'fecha_rechazo' => '2026-09-15',
            'documento' => 'RES-001',
            'observaciones' => 'Observaciones de prueba',
        ]);

    $response->assertStatus(201);
    expect($response->json()['id'])->toBe(2)
        ->and($response->json()['tipo_autorizacion'])->toBe('ITSE')
        ->and($response->json()['expediente'])->toBe(10);
});
