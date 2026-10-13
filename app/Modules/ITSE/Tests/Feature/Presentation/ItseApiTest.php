<?php
declare(strict_types=1);

use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseEstadoRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\NivelRiesgoReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ZonificacionReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\AutorizacionImprocedenteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Entities\ItseEstado;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use App\Models\User;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    DB::table('auth_user')->insert(['is_staff' => true, 'id' => 1, 'username' => 'test', 'email' => 'test@test.com', 'password' => 'secret']);
    $this->user = User::find(1);
    
    $this->mockItse = Itse::crear(
        Uuid::uuid4()->toString(),
        12345,
        CarbonImmutable::parse('2026-01-01'),
        null,
        CarbonImmutable::parse('2027-01-01'),
        'Test',
        'Dir',
        'RES-1',
        10.5,
        50,
        'REC-1',
        null,
        true,
        1,
        1,
        null,
        1,
        1,
        1,
        1,
        CarbonImmutable::now()
    );
});

it('crea un ITSE correctamente', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existeNumero')->andReturn(false);
        $mock->shouldReceive('existeRecibo')->andReturn(false);
        $mock->shouldReceive('crear')->once()->andReturn($this->mockItse);
    });
    $this->mock(ExpedienteReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existe')->andReturn(true);
    });
    $this->mock(PersonaReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existe')->andReturn(true);
    });
    $this->mock(NivelRiesgoReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existe')->andReturn(true);
    });
    $this->mock(ZonificacionReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existe')->andReturn(true);
    });
    $this->mock(AutorizacionImprocedenteReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existeParaExpedienteYTipo')->andReturn(false);
    });

    $data = [
        'numero_itse' => 12345,
        'fecha_expedicion' => '2026-01-01',
        'fecha_caducidad' => '2027-01-01',
        'nombre_comercial' => 'Test',
        'direccion' => 'Dir',
        'resolucion_numero' => 'RES-1',
        'area' => 10.5,
        'capacidad_aforo' => 10,
        'se_puede_publicar' => false,
        'numero_recibo_pago' => 'REC-1',
        'tipo_itse_id' => 1,
        'expediente_id' => 1,
        'nivel_riesgo_id' => 1,
        'conductor_id' => 1,
        'titular_id' => 1,
    ];

    $response = $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/itse/', $data);
    $response->assertStatus(201);
});

it('falla crear ITSE si no esta autenticado', function () {
    $response = $this->postJson('/api/lf-itse/itse/', []);
    $response->assertStatus(401);
});

it('falla crear ITSE si falta campos requeridos', function () {
    $response = $this->actingAs($this->user, 'api')->postJson('/api/lf-itse/itse/', []);
    $response->assertStatus(422);
});

it('actualiza un ITSE correctamente', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('buscarPorId')->andReturn($this->mockItse);
        $mock->shouldReceive('existeNumero')->andReturn(false);
        $mock->shouldReceive('existeRecibo')->andReturn(false);
        $mock->shouldReceive('actualizar')->once()->andReturn($this->mockItse);
    });
    $this->mock(PersonaReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existe')->andReturn(true);
    });
    $this->mock(NivelRiesgoReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existe')->andReturn(true);
    });

    $data = [
        'numero_itse' => 999,
        'fecha_expedicion' => '2026-01-01',
        'fecha_caducidad' => '2027-01-01',
        'nombre_comercial' => 'NEW NAME',
        'direccion' => 'DIR',
        'resolucion_numero' => 'RES-1',
        'area' => 10.5,
        'capacidad_aforo' => 10,
        'se_puede_publicar' => false,
        'numero_recibo_pago' => 'REC1',
        'tipo_itse_id' => 1,
        'nivel_riesgo_id' => 1,
        'conductor_id' => 1,
        'titular_id' => 1,
    ];

    $response = $this->actingAs($this->user, 'api')->putJson("/api/lf-itse/itse/1/", $data);
    $response->assertStatus(200);
});

it('obtiene un ITSE correctamente', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('buscarPorId')->zeroOrMoreTimes()->andReturn($this->mockItse);
    });

    $response = $this->actingAs($this->user, 'api')->getJson("/api/lf-itse/itse/1/");
    $response->assertStatus(200);
});

it('busca un ITSE por filtro', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('buscarPorFiltro')->once()->andReturn([['numero_itse' => 222, 'expediente_id' => 1, 'titular_id' => 1, 'fecha_expedicion' => '2026-01-01', 'fecha_caducidad' => '2026-01-01', 'nombre_comercial' => 'test']]);
    });

    $response = $this->actingAs($this->user, 'api')->getJson("/api/lf-itse/itse/buscar/?filtro=NUMERO&valor=123");
    $response->assertStatus(200);
});

it('verificar expediente GET', function () {
    $this->mock(ExpedienteReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('buscarPorNumeroYAnio')->once()->andReturn((object)["id" => 1]);
    });
    $this->mock(AutorizacionImprocedenteReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existe')->once()->andReturn(false);
    });
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('buscarPorFiltro')->once()->andReturn([]);
    });

    $response = $this->actingAs($this->user, 'api')->getJson("/api/lf-itse/itse/verificar-expediente/?numero_expediente=123&anio=2026");
    $response->assertStatus(200);
});

it('verificar expediente POST', function () {
    $this->mock(ExpedienteReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('buscarPorNumeroYAnio')->once()->andReturn((object)["id" => 1]);
    });
    $this->mock(AutorizacionImprocedenteReadRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('existe')->once()->andReturn(false);
    });
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('buscarPorFiltro')->once()->andReturn([]);
    });

    $response = $this->actingAs($this->user, 'api')->postJson("/api/lf-itse/itse/verificar-expediente/", [
        'numero_expediente' => 123,
        'anio' => 2026
    ]);
    $response->assertStatus(200);
});

it('notifica ITSE', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('buscarPorId')->zeroOrMoreTimes()->andReturn($this->mockItse);
        $mock->shouldReceive('actualizar')->once()->andReturn($this->mockItse);
    });

    $response = $this->actingAs($this->user, 'api')->postJson("/api/lf-itse/itse/1/notificacion/", [
        'fecha_notificacion' => '2026-02-01'
    ]);
    $response->assertStatus(200);
});

it('inactivar ITSE', function () {
    $this->mock(ItseRepositoryInterface::class, function ($mock) {
        $mock->shouldReceive('buscarPorId')->zeroOrMoreTimes()->andReturn($this->mockItse);
    });
    $estadoId = DB::table('estados')->insertGetId(['nombre' => 'CLAUSURA', 'es_para_lf' => false, 'es_para_itse' => true, 'esta_activo' => false]);
    $this->mock(ItseEstadoRepositoryInterface::class, function ($mock) use ($estadoId) {
        $mock->shouldReceive('tieneEstadoInactivo')->andReturn(false);
        $mock->shouldReceive('crear')->once()
            ->with(Mockery::on(fn (ItseEstado $e) => $e->estadoId === $estadoId
                && $e->fechaEstado->format('Y-m-d') === '2026-03-01'
                && $e->documento === 'DOC'))
            ->andReturnArg(0);
    });

    $response = $this->actingAs($this->user, 'api')->postJson("/api/lf-itse/itse/inactivar/", [
        'itse_id' => 1,
        'estado_id' => $estadoId,
        'fecha_estado' => '2026-03-01',
        'documento' => 'DOC',
        'observaciones' => 'OBS',
    ]);
    $response->assertStatus(200);
});

it('devuelve 401 para todos los endpoints sin auth', function () {
    $this->postJson('/api/lf-itse/itse/')->assertStatus(401);
    $this->putJson('/api/lf-itse/itse/1/')->assertStatus(401);
    $this->getJson('/api/lf-itse/itse/1/')->assertStatus(401);
    $this->getJson('/api/lf-itse/itse/buscar/')->assertStatus(401);
    $this->getJson('/api/lf-itse/itse/verificar-expediente/')->assertStatus(401);
    $this->postJson('/api/lf-itse/itse/1/notificacion/')->assertStatus(401);
    $this->postJson('/api/lf-itse/itse/inactivar/')->assertStatus(401);
});
