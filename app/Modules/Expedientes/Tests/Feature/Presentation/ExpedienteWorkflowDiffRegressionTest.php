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

it('ampliacion-plazo response matches exact 19 keys validated against Django', function () {
    $expOriginal = new Expediente(
        id: 27,
        uuid: 'c19fe3b5-05a6-401f-9e34-86c03effb0d4',
        numeroExpediente: 999000,
        fechaRecepcion: new DateTimeImmutable('2026-09-23 10:00:00+00'),
        tipoProcedimientoTupaId: 19,
        solicitanteId: 1,
        representanteId: null,
        observaciones: 'TEST WORKFLOW 7C',
        fechaVencimiento: new DateTimeImmutable('2026-09-25'),
        fechaAlerta: new DateTimeImmutable('2026-09-24'),
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2026-09-23 10:00:00+00'),
        autorizacionesImprocedentes: [],
    );

    $this->repo->shouldReceive('buscarPorId')->with(27)->once()->andReturn($expOriginal);
    $this->tiposTupa->shouldReceive('buscarPorId')->with(19)->once()->andReturn((object) [
        'id' => 19,
        'dias_alerta_vencimiento' => 1,
    ]);
    $this->repo->shouldReceive('actualizar')->once()->andReturnArg(0);

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/27/ampliacion-plazo/', [
            'fecha_suspension' => '2026-09-23',
            'dias_ampliacion' => 5,
            'motivo_ampliacion' => 'Suspensión por documento pendiente',
        ]);

    $response->assertStatus(200);
    $data = $response->json();

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

    expect(array_keys($data))->toBe($expectedKeys)
        ->and($data['id'])->toBe(27)
        ->and($data['dias_ampliacion'])->toBe(5)
        ->and($data['motivo_ampliacion'])->toBe('Suspensión por documento pendiente')
        ->and($data['fecha_vencimiento'])->toBe('2026-10-02')
        ->and($data['fecha_alerta'])->toBe('2026-10-01')
        ->and($data['fecha_suspension'])->toBe('2026-09-23');
});

it('denegar-licencia and denegar-itse responses match exact 8 keys validated against Django', function () {
    $expOriginal = new Expediente(
        id: 27,
        uuid: 'c19fe3b5-05a6-401f-9e34-86c03effb0d4',
        numeroExpediente: 999000,
        fechaRecepcion: new DateTimeImmutable('2026-09-23 10:00:00+00'),
        tipoProcedimientoTupaId: 19,
        solicitanteId: 1,
        representanteId: null,
        observaciones: 'TEST',
        fechaVencimiento: new DateTimeImmutable('2026-09-25'),
        fechaAlerta: new DateTimeImmutable('2026-09-24'),
        fechaSuspension: null,
        diasAmpliacion: null,
        motivoAmpliacion: null,
        usuarioAmpliacionId: null,
        fechaDigitacionAmpliacion: null,
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2026-09-23 10:00:00+00'),
        autorizacionesImprocedentes: [],
    );

    $this->repo->shouldReceive('buscarPorId')->with(27)->andReturn($expOriginal);
    $this->repo->shouldReceive('tieneLicencia')->with(27)->andReturnFalse();

    $auto = new AutorizacionImprocedente(
        id: 4,
        expedienteId: 27,
        tipoAutorizacion: TipoAutorizacion::LF,
        fechaRechazo: new DateTimeImmutable('2026-09-23'),
        documento: 'RES-DENEGAR-001',
        observaciones: 'Rechazado por zonificación no conforme',
        usuarioId: 1,
        fechaDigitacion: new DateTimeImmutable('2026-09-23 16:56:37-05:00'),
    );
    $this->autorizacionesRepo->shouldReceive('crear')->once()->andReturn($auto);

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/27/denegar-licencia/', [
            'fecha_rechazo' => '2026-09-23',
            'documento' => 'RES-DENEGAR-001',
            'observaciones' => 'Rechazado por zonificación no conforme',
        ]);

    $response->assertStatus(201);
    $data = $response->json();

    $expectedKeys = [
        'id',
        'tipo_autorizacion',
        'fecha_rechazo',
        'documento',
        'observaciones',
        'fecha_digitacion',
        'expediente',
        'usuario',
    ];

    expect(array_keys($data))->toBe($expectedKeys)
        ->and($data['id'])->toBe(4)
        ->and($data['tipo_autorizacion'])->toBe('LF')
        ->and($data['fecha_rechazo'])->toBe('2026-09-23')
        ->and($data['documento'])->toBe('RES-DENEGAR-001')
        ->and($data['observaciones'])->toBe('Rechazado por zonificación no conforme')
        ->and($data['expediente'])->toBe(27)
        ->and($data['usuario'])->toBe(1);
});
