<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Tests\Feature\Presentation;

use App\Models\User;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
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

    $this->repo = Mockery::mock(ExpedienteRepositoryInterface::class);
    $this->app->instance(ExpedienteRepositoryInterface::class, $this->repo);

    $this->feriados = Mockery::mock(FeriadoReadRepositoryInterface::class);
    $this->feriados->shouldReceive('esFeriado')->andReturn(false)->byDefault();
    $this->app->instance(FeriadoReadRepositoryInterface::class, $this->feriados);
});

afterEach(function () {
    Mockery::close();
});

it('pendientes endpoint response structure matches exact 11 keys from Django', function () {
    $this->repo->shouldReceive('listarPendientes')->once()->andReturn([
        [
            'id' => 25,
            'numero_expediente' => 888888,
            'fecha_recepcion' => '2026-09-20 15:30:00+00',
            'nombre' => 'Licencia riesgo bajo con ITSE posterior',
            'fecha_vencimiento' => '2026-10-20',
            'fecha_alerta' => '2026-10-15',
            'persona_nombre' => 'TORRES TUESTA DE TERRONES SILVIA DEL PILAR',
            'licencia_pendiente' => true,
            'itse_pendiente' => true,
            'mostrar_alerta' => false,
        ],
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/pendientes/');

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(1);

    $item = $data[0];
    $expectedKeys = [
        'id',
        'numero_expediente',
        'fecha_recepcion',
        'nombre',
        'fecha_vencimiento',
        'fecha_alerta',
        'persona_nombre',
        'licencia_pendiente',
        'itse_pendiente',
        'mostrar_alerta',
        'dias_habiles_restantes',
    ];

    expect(array_keys($item))->toBe($expectedKeys)
        ->and($item['id'])->toBe(25)
        ->and($item['numero_expediente'])->toBe(888888)
        ->and($item['fecha_recepcion'])->toBe('2026-09-20T15:30:00Z')
        ->and($item['nombre'])->toBe('Licencia riesgo bajo con ITSE posterior')
        ->and($item['fecha_vencimiento'])->toBe('2026-10-20')
        ->and($item['fecha_alerta'])->toBe('2026-10-15')
        ->and($item['persona_nombre'])->toBe('TORRES TUESTA DE TERRONES SILVIA DEL PILAR')
        ->and($item['licencia_pendiente'])->toBeTrue()
        ->and($item['itse_pendiente'])->toBeTrue()
        ->and($item['mostrar_alerta'])->toBeFalse()
        ->and($item['dias_habiles_restantes'])->toBeInt();
});

it('buscar endpoint response structure matches exact 27 keys from Django', function () {
    $this->repo->shouldReceive('buscarPorFiltro')
        ->once()
        ->with('NUMERO', '888888')
        ->andReturn([
            [
                'id' => 25,
                'tipo_procedimiento_tupa_id' => 19,
                'numero_expediente' => 888888,
                'fecha_recepcion' => '2026-09-20 15:30:00+00',
                'solicitante_id' => 4,
                'representante_id' => null,
                'observaciones' => 'EXPEDIENTE TEST 7B UNO',
                'fecha_vencimiento' => '2026-10-20',
                'fecha_alerta' => '2026-10-15',
                'fecha_suspension' => null,
                'dias_ampliacion' => null,
                'motivo_ampliacion' => null,
                'usuario_ampliacion' => null,
                'fecha_digitacion_ampliacion' => null,
                'usuario_id' => 1,
                'fecha_digitacion' => '2026-09-20 15:30:00+00',
                'nombre_procedimiento' => 'Licencia riesgo bajo con ITSE posterior',
                'plazo_atencion_dias' => 2,
                'dias_alerta_vencimiento' => 1,
                'requiere_itse' => true,
                'requiere_lf' => true,
                'solicitante_nombre' => 'TORRES TUESTA DE TERRONES SILVIA DEL PILAR',
                'solicitante_ruc' => '10009545889',
                'licencia_pendiente' => true,
                'itse_pendiente' => true,
                'mostrar_alerta' => false,
            ],
        ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/buscar/?filtro=NUMERO&valor=888888');

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(1);

    $item = $data[0];
    $expectedKeys = [
        'id',
        'tipo_procedimiento_tupa_id',
        'numero_expediente',
        'fecha_recepcion',
        'solicitante_id',
        'representante_id',
        'observaciones',
        'fecha_vencimiento',
        'fecha_alerta',
        'fecha_suspension',
        'dias_ampliacion',
        'motivo_ampliacion',
        'usuario_ampliacion',
        'fecha_digitacion_ampliacion',
        'usuario_id',
        'fecha_digitacion',
        'nombre_procedimiento',
        'plazo_atencion_dias',
        'dias_alerta_vencimiento',
        'requiere_itse',
        'requiere_lf',
        'solicitante_nombre',
        'solicitante_ruc',
        'licencia_pendiente',
        'itse_pendiente',
        'mostrar_alerta',
        'dias_habiles_restantes',
    ];

    expect(array_keys($item))->toBe($expectedKeys)
        ->and($item['id'])->toBe(25)
        ->and($item['tipo_procedimiento_tupa_id'])->toBe(19)
        ->and($item['numero_expediente'])->toBe(888888)
        ->and($item['fecha_recepcion'])->toBe('2026-09-20T15:30:00Z')
        ->and($item['solicitante_id'])->toBe(4)
        ->and($item['representante_id'])->toBeNull()
        ->and($item['observaciones'])->toBe('EXPEDIENTE TEST 7B UNO')
        ->and($item['fecha_vencimiento'])->toBe('2026-10-20')
        ->and($item['fecha_alerta'])->toBe('2026-10-15')
        ->and($item['fecha_suspension'])->toBeNull()
        ->and($item['dias_ampliacion'])->toBeNull()
        ->and($item['motivo_ampliacion'])->toBeNull()
        ->and($item['usuario_ampliacion'])->toBeNull()
        ->and($item['fecha_digitacion_ampliacion'])->toBeNull()
        ->and($item['usuario_id'])->toBe(1)
        ->and($item['fecha_digitacion'])->toBe('2026-09-20T15:30:00Z')
        ->and($item['nombre_procedimiento'])->toBe('Licencia riesgo bajo con ITSE posterior')
        ->and($item['plazo_atencion_dias'])->toBe(2)
        ->and($item['dias_alerta_vencimiento'])->toBe(1)
        ->and($item['requiere_itse'])->toBeTrue()
        ->and($item['requiere_lf'])->toBeTrue()
        ->and($item['solicitante_nombre'])->toBe('TORRES TUESTA DE TERRONES SILVIA DEL PILAR')
        ->and($item['solicitante_ruc'])->toBe('10009545889')
        ->and($item['licencia_pendiente'])->toBeTrue()
        ->and($item['itse_pendiente'])->toBeTrue()
        ->and($item['mostrar_alerta'])->toBeFalse()
        ->and($item['dias_habiles_restantes'])->toBeInt();
});

it('consulta endpoint response structure matches exact 19 keys from Django', function () {
    $expedienteEntity = new Expediente(
        id: 25,
        uuid: '8cbce9a5-3509-42bd-9cab-659f0239ac9d',
        numeroExpediente: 888888,
        fechaRecepcion: new DateTimeImmutable('2026-09-20 10:30:00-05:00'),
        tipoProcedimientoTupaId: 19,
        solicitanteId: 4,
        representanteId: null,
        observaciones: 'EXPEDIENTE TEST 7B UNO',
        fechaVencimiento: new DateTimeImmutable('2026-10-20'),
        fechaAlerta: new DateTimeImmutable('2026-10-15'),
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2026-09-20 10:30:00-05:00'),
        autorizacionesImprocedentes: [],
    );

    $this->repo->shouldReceive('consultar')
        ->once()
        ->with(888888)
        ->andReturn([$expedienteEntity]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/consulta/?numero_expediente=888888');

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(1);

    $item = $data[0];
    $expectedKeys = [
        'id',
        'archivos',
        'autorizaciones_improcedentes',
        'uuid',
        'numero_expediente',
        'fecha_recepcion',
        'observaciones',
        'fecha_vencimiento',
        'fecha_alerta',
        'fecha_suspension',
        'dias_ampliacion',
        'motivo_ampliacion',
        'fecha_digitacion_ampliacion',
        'fecha_digitacion',
        'tipo_procedimiento_tupa',
        'solicitante',
        'representante',
        'usuario_ampliacion',
        'usuario',
    ];

    expect(array_keys($item))->toBe($expectedKeys)
        ->and($item['id'])->toBe(25)
        ->and($item['archivos'])->toBe([])
        ->and($item['autorizaciones_improcedentes'])->toBe([])
        ->and($item['uuid'])->toBe('8cbce9a5-3509-42bd-9cab-659f0239ac9d')
        ->and($item['numero_expediente'])->toBe(888888)
        ->and($item['fecha_recepcion'])->toBe('2026-09-20T10:30:00-05:00')
        ->and($item['observaciones'])->toBe('EXPEDIENTE TEST 7B UNO')
        ->and($item['fecha_vencimiento'])->toBe('2026-10-20')
        ->and($item['fecha_alerta'])->toBe('2026-10-15')
        ->and($item['fecha_suspension'])->toBeNull()
        ->and($item['dias_ampliacion'])->toBeNull()
        ->and($item['motivo_ampliacion'])->toBeNull()
        ->and($item['fecha_digitacion_ampliacion'])->toBeNull()
        ->and($item['fecha_digitacion'])->toBe('2026-09-20T10:30:00-05:00')
        ->and($item['tipo_procedimiento_tupa'])->toBe(19)
        ->and($item['solicitante'])->toBe(4)
        ->and($item['representante'])->toBeNull()
        ->and($item['usuario_ampliacion'])->toBeNull()
        ->and($item['usuario'])->toBe(1);
});
