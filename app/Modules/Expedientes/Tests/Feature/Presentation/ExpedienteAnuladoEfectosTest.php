<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Licencias\Application\DTOs\CrearLicenciaDTO;
use App\Modules\Licencias\Application\UseCases\CrearLicenciaUseCase;
use App\Modules\Licencias\Domain\Exceptions\ExpedienteNoValido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Un expediente anulado queda solo como constancia: sale de todo lo "en trámite"
 * y no admite cambios ni nuevas autorizaciones.
 */
beforeEach(function () {
    DB::table('auth_user')->insert(UsuarioAuth::datos(['id' => 1, 'username' => 'admin', 'is_staff' => true, 'is_superuser' => true]));
    $this->user = User::find(1);

    $tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa([
        'requiere_lf' => true, 'requiere_itse' => true, 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 3,
    ]));
    $ruc = DB::table('tipos_documento_identidad')->insertGetId(Registros::tipoDocumentoIdentidad(['codigo' => '06']));
    $persona = DB::table('personas')->insertGetId(Registros::persona(['nombres' => 'COMERCIAL SELVA', 'tipo_persona' => 'J']));
    DB::table('personas_documentos')->insert(['numero_documento' => '20123456789', 'persona_id' => $persona, 'tipo_documento_identidad_id' => $ruc]);

    // Dos expedientes en trámite, ya en alerta; uno se anula
    $nuevo = fn (int $numero) => DB::table('expedientes')->insertGetId(Registros::expediente([
        'numero_expediente' => $numero, 'solicitante_id' => $persona, 'tipo_procedimiento_tupa_id' => $tupa,
        'fecha_alerta' => now()->subDay()->toDateString(), 'fecha_vencimiento' => now()->addDays(2)->toDateString(),
    ]));
    $this->vigente = $nuevo(501);
    $this->anulado = $nuevo(502);

    $this->actingAs($this->user, 'api')
        ->postJson("/api/lf-itse/expedientes/{$this->anulado}/anular/", ['motivo' => 'Desistimiento del solicitante'])
        ->assertOk();
});

it('sale de pendientes, del dashboard y de la búsqueda como pendiente', function () {
    $pendientes = $this->actingAs($this->user, 'api')->getJson('/api/lf-itse/expedientes/pendientes/')->assertOk()->json();
    expect(array_column($pendientes, 'numero_expediente'))->toBe([501]);

    $this->actingAs($this->user, 'api')->getJson('/api/lf-itse/dashboard/resumen/')
        ->assertOk()
        ->assertJsonPath('expedientes.en_tramite', 1);

    $busqueda = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/buscar/?filtro=NUMERO&valor=502')
        ->assertOk()
        ->json();
    expect($busqueda[0])->toMatchArray([
        'anulado' => true,
        'motivo_anulacion' => 'Desistimiento del solicitante',
        'licencia_pendiente' => false,
        'itse_pendiente' => false,
        'mostrar_alerta' => false,
    ]);
});

it('aparece en el listado marcado como anulado', function () {
    $lista = collect($this->actingAs($this->user, 'api')->getJson('/api/lf-itse/expedientes/')->assertOk()->json());

    expect($lista->firstWhere('numero_expediente', 502))->toMatchArray(['anulado' => true])
        ->and($lista->firstWhere('numero_expediente', 502)['anulacion']['motivo'])->toBe('Desistimiento del solicitante')
        ->and($lista->firstWhere('numero_expediente', 501))->toMatchArray(['anulado' => false, 'anulacion' => null]);
});

it('no admite cambios, ampliación de plazo ni denegatorias', function () {
    $mensaje = ['error' => 'El expediente N° 502 está anulado: no admite cambios ni nuevos trámites.'];
    $url = "/api/lf-itse/expedientes/{$this->anulado}/";
    $pedir = fn (string $metodo, string $ruta, array $datos) => $this->actingAs($this->user, 'api')->json($metodo, $ruta, $datos);

    $pedir('PUT', $url, [
        'numero_expediente' => 502, 'fecha_recepcion' => '2026-10-01T10:00:00',
        'tipo_procedimiento_tupa_id' => DB::table('tipos_procedimiento_tupa')->value('id'),
        'solicitante_id' => DB::table('personas')->value('id'),
    ])->assertStatus(409)->assertJson($mensaje);
    $pedir('POST', $url.'ampliacion-plazo/', ['fecha_suspension' => '2026-10-10', 'dias_ampliacion' => 5, 'motivo_ampliacion' => 'Subsanación'])
        ->assertStatus(409)->assertJson($mensaje);
    $pedir('POST', $url.'denegar-licencia/', ['fecha_rechazo' => '2026-10-10', 'documento' => 'RG 1', 'observaciones' => 'x'])
        ->assertStatus(409)->assertJson($mensaje);
    $pedir('POST', $url.'denegar-itse/', ['fecha_rechazo' => '2026-10-10', 'documento' => 'RG 1', 'observaciones' => 'x'])
        ->assertStatus(409)->assertJson($mensaje);

    expect(DB::table('autorizaciones_improcedentes')->count())->toBe(0);
});

it('no permite emitir una licencia sobre él', function () {
    $datos = [
        'usuario_id' => 1, 'numero_licencia' => 900, 'nombre_comercial' => 'BODEGA', 'tipo_licencia_id' => 1,
        'titular_id' => 1, 'expediente_id' => $this->anulado, 'nivel_riesgo_id' => 1, 'zonificacion_id' => 1,
    ];

    expect(fn () => app(CrearLicenciaUseCase::class)->execute(CrearLicenciaDTO::fromArray($datos)))
        ->toThrow(ExpedienteNoValido::class, "El expediente con ID {$this->anulado} está anulado");
    expect(DB::table('licencias_funcionamiento')->count())->toBe(0);
});

it('el ciudadano lo ve como ANULADO en la consulta pública', function () {
    $this->getJson('/api/publico/consulta/?tipo=expediente&numero=502&documento=20123456789')
        ->assertOk()
        ->assertJsonPath('0.estado', 'ANULADO');
});
