<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Dashboard\Application\UseCases\ObtenerResumenGeneralUseCase;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\ITSE\Application\DTOs\PorRenovarItseDTO;
use App\Modules\ITSE\Application\UseCases\ListarPorRenovarItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentExpedienteReadRepository as ExpedienteItse;
use App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel;
use App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentExpedienteReadRepository as ExpedienteLicencia;
use App\Modules\Reportes\Application\UseCases\GenerarInformeLicenciasUseCase;
use App\Modules\Reportes\Domain\Contracts\GeneradorPdfInterface;
use App\Modules\Reportes\Domain\Exceptions\InformeInvalido;
use App\Modules\Reportes\Domain\ValueObjects\Periodo;
use App\Shared\Domain\Calendario;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;

/**
 * El martes 3 de noviembre a las 20:00 de Lima ya es el miércoles 4 a la 01:00 en UTC, la zona
 * global del sistema. Todo "hoy" debe seguir siendo el martes.
 */
function congelarEnLima(string $momento): void
{
    $instante = CarbonImmutable::parse($momento, 'America/Lima');
    CarbonImmutable::setTestNow($instante);
    Carbon::setTestNow($instante);
}

beforeEach(function () {
    congelarEnLima('2026-11-03 20:00:00');
    RateLimiter::clear('consulta-publica');

    DB::table('auth_user')->insert(UsuarioAuth::datos(['id' => 1, 'username' => 'admin', 'is_staff' => true, 'is_superuser' => true]));
    $this->admin = User::find(1);

    $this->tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa([
        'requiere_lf' => true, 'requiere_itse' => true, 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 3,
    ]));
    $this->persona = DB::table('personas')->insertGetId(Registros::persona());
    DB::table('personas_documentos')->insert([
        'numero_documento' => '12345678', 'persona_id' => $this->persona, 'tipo_documento_identidad_id' => 1,
    ]);

    $this->expediente = fn (array $datos = []) => DB::table('expedientes')->insertGetId(Registros::expediente(array_merge([
        'solicitante_id' => $this->persona, 'tipo_procedimiento_tupa_id' => $this->tupa,
    ], $datos)));
    $this->consultar = fn (string $tipo, int $numero) => $this->getJson('/api/publico/consulta/?'.http_build_query([
        'tipo' => $tipo, 'numero' => $numero, 'documento' => '12345678',
    ]))->assertOk()->json('0');
});

afterEach(function () {
    CarbonImmutable::setTestNow();
    Carbon::setTestNow();
});

it('calcula hoy con el día de Lima, antes y después de las 19:00', function (string $momento, string $hoy) {
    congelarEnLima($momento);

    expect(Calendario::hoy()->toDateString())->toBe($hoy);
})->with([
    'a las 18:59' => ['2026-11-03 18:59:59', '2026-11-03'],
    'a las 20:00' => ['2026-11-03 20:00:00', '2026-11-03'],
    'a las 23:59' => ['2026-11-03 23:59:59', '2026-11-03'],
    'ya es el día siguiente' => ['2026-11-04 00:00:00', '2026-11-04'],
]);

it('consulta pública: una autorización sigue vigente en su último día', function () {
    DB::table('licencias_funcionamiento')->insert(Registros::licencia([
        'numero_licencia' => 501, 'titular_id' => $this->persona, 'es_vigencia_indeterminada' => false,
        'fecha_inicio_vigencia' => '2026-01-01', 'fecha_fin_vigencia' => '2026-11-03',
    ]));
    DB::table('licencias_funcionamiento')->insert(Registros::licencia([
        'numero_licencia' => 502, 'titular_id' => $this->persona, 'es_vigencia_indeterminada' => false,
        'fecha_inicio_vigencia' => '2026-01-01', 'fecha_fin_vigencia' => '2026-11-02',
    ]));
    DB::table('itse')->insert(Registros::itse([
        'numero_itse' => 601, 'titular_id' => $this->persona, 'fecha_expedicion' => '2024-11-03', 'fecha_caducidad' => '2026-11-03',
    ]));

    expect(($this->consultar)('licencia', 501)['estado'])->toBe('VIGENTE')
        ->and(($this->consultar)('licencia', 502)['estado'])->toBe('VENCIDO')
        ->and(($this->consultar)('itse', 601)['estado'])->toBe('VIGENTE');
});

it('consulta pública: la fecha de ingreso del expediente es la de Lima', function () {
    ($this->expediente)(['numero_expediente' => 701, 'fecha_recepcion' => '2026-11-04 01:00:00+00']);

    expect(($this->consultar)('expediente', 701)['fecha_emision'])->toBe('2026-11-03');
});

it('dashboard: cuenta con el día y el mes de Lima', function () {
    ($this->expediente)(['fecha_alerta' => '2026-11-03', 'fecha_vencimiento' => '2026-11-03']);
    DB::table('itse')->insert(Registros::itse(['fecha_expedicion' => '2024-11-03', 'fecha_caducidad' => '2026-11-03']));

    $resumen = app(ObtenerResumenGeneralUseCase::class)->execute();

    expect($resumen->expedientesVencidos)->toBe(0)
        ->and($resumen->expedientesPorVencer)->toBe(1)
        ->and($resumen->itseVigentes)->toBe(1);

    // Última noche del mes: en UTC ya sería noviembre
    congelarEnLima('2026-10-31 20:00:00');
    DB::table('licencias_funcionamiento')->insert(Registros::licencia(['fecha_emision' => '2026-10-31']));

    expect(app(ObtenerResumenGeneralUseCase::class)->execute()->licenciasEmitidasMes)->toBe(1);
});

it('ITSE por renovar: el rango empieza hoy en Lima', function () {
    $itses = Mockery::mock(ItseRepositoryInterface::class);
    $itses->shouldReceive('listarPorRenovar')->once()->with('2026-11-03', '2026-12-03')->andReturn([]);

    (new ListarPorRenovarItseUseCase($itses))->execute(new PorRenovarItseDTO(fechaDesde: null, fechaHasta: null));
});

it('reportes: el mes en curso es el de Lima', function () {
    congelarEnLima('2026-10-31 20:00:00');

    $periodo = Periodo::oMesActual(null, null);

    expect($periodo->desde)->toBe('2026-10-01')->and($periodo->hasta)->toBe('2026-10-31');
});

it('informe PDF: lleva la fecha de Lima y rechaza el mes que aún no empieza en Lima', function () {
    $pdf = new class implements GeneradorPdfInterface
    {
        /** @var array<string, mixed> */
        public array $datos = [];

        public function generar(string $plantilla, array $datos, bool $horizontal = false): string
        {
            $this->datos = $datos;

            return '%PDF-falso';
        }
    };
    $this->app->instance(GeneradorPdfInterface::class, $pdf);

    app(GenerarInformeLicenciasUseCase::class)->execute('2026-10-01', '2026-10-31', 1);

    expect($pdf->datos['fechaEmision'])->toBe('3 de noviembre de 2026')
        ->and($pdf->datos['generado'])->toBe('03/11/2026 20:00');

    congelarEnLima('2026-10-31 20:00:00');
    expect(fn () => app(GenerarInformeLicenciasUseCase::class)->execute('2026-11-01', '2026-11-30', 1))
        ->toThrow(InformeInvalido::class);
});

it('licencia: la fecha de emisión se guarda con el día de Lima', function () {
    $licencia = new LicenciaModel;
    $licencia->fecha_emision = CarbonImmutable::now('America/Lima');

    expect($licencia->getAttributes()['fecha_emision'])->toStartWith('2026-11-03');
});

it('expedientes pendientes y búsqueda: alerta y días restantes con el día de Lima', function () {
    ($this->expediente)(['numero_expediente' => 701, 'fecha_alerta' => '2026-11-03', 'fecha_vencimiento' => '2026-11-03']);
    ($this->expediente)(['numero_expediente' => 702, 'fecha_alerta' => '2026-11-04', 'fecha_vencimiento' => '2026-11-04']);

    $pendientes = collect($this->actingAs($this->admin, 'api')->getJson('/api/lf-itse/expedientes/pendientes/')->assertOk()->json())
        ->keyBy('numero_expediente');

    expect($pendientes[701])->toMatchArray(['mostrar_alerta' => true, 'dias_habiles_restantes' => 0])
        ->and($pendientes[702])->toMatchArray(['mostrar_alerta' => false, 'dias_habiles_restantes' => 1]);

    $buscar = fn (int $numero) => $this->actingAs($this->admin, 'api')
        ->getJson('/api/lf-itse/expedientes/buscar/?filtro=NUMERO&valor='.$numero)->assertOk()->json('0');

    expect($buscar(701))->toMatchArray(['mostrar_alerta' => true, 'dias_habiles_restantes' => 0])
        ->and($buscar(702))->toMatchArray(['mostrar_alerta' => false, 'dias_habiles_restantes' => 1]);
});

it('búsqueda por fecha de recepción: usa el día de Lima', function () {
    ($this->expediente)(['numero_expediente' => 701, 'fecha_recepcion' => '2026-11-04 01:00:00+00']);

    $buscar = fn (string $dia) => $this->actingAs($this->admin, 'api')
        ->getJson('/api/lf-itse/expedientes/buscar/?filtro=FECHA_RECEPCION&valor='.$dia)->assertOk()->json();

    expect(array_column($buscar('2026-11-03'), 'numero_expediente'))->toBe([701])
        ->and($buscar('2026-11-04'))->toBe([]);
});

it('expedientes por inspeccionar: la alerta usa el día de Lima', function () {
    ($this->expediente)(['numero_expediente' => 701, 'fecha_alerta' => '2026-11-04', 'fecha_vencimiento' => '2026-11-10']);

    $fila = $this->actingAs($this->admin, 'api')->getJson('/api/lf-itse/inspecciones/por-inspeccionar/')->assertOk()->json('0');

    expect($fila['mostrar_alerta'])->toBeFalse();
});

it('asignación: acepta programar la visita para hoy en Lima y rechaza ayer', function () {
    DB::table('auth_user')->insert(UsuarioAuth::datos(['id' => 2, 'username' => 'inspector']));
    DB::table('usuarios_perfiles')->insert([
        'expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false,
        'es_inspector' => true, 'es_jefe_itse' => false, 'reportes_completos' => false,
        'user_id' => 2, 'fecha_digitacion' => now(), 'user_id_digitador' => 1,
    ]);
    $datos = [
        'expediente_id' => ($this->expediente)(), 'inspector_id' => 2,
        'nombre_comercial' => 'BODEGA', 'direccion' => 'JR. LIMA 1',
    ];
    $asignar = fn (string $dia) => $this->actingAs($this->admin, 'api')
        ->postJson('/api/lf-itse/inspecciones/asignaciones/', [...$datos, 'fecha_programada' => $dia]);

    $asignar('2026-11-02')->assertStatus(422)->assertJsonValidationErrors('fecha_programada');
    $asignar('2026-11-03')->assertCreated();
});

it('denegatoria: la fecha de rechazo no puede ser mañana en Lima', function () {
    $id = ($this->expediente)();
    $denegar = fn (string $dia) => $this->actingAs($this->admin, 'api')->postJson("/api/lf-itse/expedientes/{$id}/denegar-licencia/", [
        'fecha_rechazo' => $dia, 'documento' => 'RG 1', 'observaciones' => 'No procede',
    ]);

    $denegar('2026-11-04')->assertStatus(400)->assertJsonStructure(['fecha_rechazo']);
    $denegar('2026-11-03')->assertSuccessful();
});

it('número por año: un expediente del 31 de diciembre en la noche es del año que termina', function () {
    congelarEnLima('2026-12-31 20:00:00');
    ($this->expediente)(['numero_expediente' => 77, 'fecha_recepcion' => now()]);
    $repositorio = app(ExpedienteRepositoryInterface::class);

    expect($repositorio->existeNumeroEnAnio(77, 2026))->toBeTrue()
        ->and($repositorio->existeNumeroEnAnio(77, 2027))->toBeFalse()
        ->and($repositorio->siguienteNumeroEnAnio(2026))->toBe(78)
        ->and($repositorio->siguienteNumeroEnAnio(2027))->toBe(1)
        ->and((new ExpedienteItse)->buscarPorNumeroYAnio(77, 2026))->not->toBeNull()
        ->and((new ExpedienteItse)->buscarPorNumeroYAnio(77, 2027))->toBeNull()
        ->and((new ExpedienteLicencia)->buscarPorNumeroYAnio(77, 2026))->not->toBeNull()
        ->and((new ExpedienteLicencia)->buscarPorNumeroYAnio(77, 2027))->toBeNull();

    // Aunque el cliente la envíe en UTC, el número y la fecha salen del día de Lima
    $this->actingAs($this->admin, 'api')->postJson('/api/lf-itse/expedientes/', [
        'fecha_recepcion' => '2027-01-01T01:30:00Z',
        'tipo_procedimiento_tupa_id' => $this->tupa, 'solicitante_id' => $this->persona,
    ])->assertCreated()->assertJsonPath('numero_expediente', 78)
        ->assertJsonPath('fecha_recepcion', '2026-12-31T20:30:00-05:00');
});
