<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

const URL_REPORTES = '/api/lf-itse/reportes/';

beforeEach(function () {
    /** Personal con los accesos indicados */
    $this->personal = function (array $accesos, array $datos = []): User {
        $id = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(array_merge(['username' => 'p_'.uniqid(), 'is_staff' => true], $datos)));
        DB::table('usuarios_perfiles')->insert(array_merge(
            ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false],
            $accesos,
            ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
        ));

        return User::find($id);
    };
    $this->registrador = ($this->personal)(['licencias' => true, 'itse' => true], ['first_name' => 'ANA', 'last_name' => 'TORRES']);

    // Mismos estados que el catálogo real (seeders/sql/catalogos.sql)
    foreach ([[101, 'Vigente', true, true, true], [102, 'Transferida', true, false, true], [103, 'Anulada', true, true, false], [104, 'Revocada', true, true, false]] as [$id, $nombre, $lf, $itse, $activo]) {
        DB::table('estados')->insert(Registros::estado(['id' => $id, 'nombre' => $nombre, 'es_para_lf' => $lf, 'es_para_itse' => $itse, 'esta_activo' => $activo]));
    }

    $ruc = DB::table('tipos_documento_identidad')->insertGetId(Registros::tipoDocumentoIdentidad(['codigo' => '06', 'nombre' => 'RUC']));
    $this->titular = DB::table('personas')->insertGetId(Registros::persona(['tipo_persona' => 'J', 'nombres' => 'COMERCIAL SELVA S.A.C.']));
    DB::table('personas_documentos')->insert(['numero_documento' => '20123456789', 'persona_id' => $this->titular, 'tipo_documento_identidad_id' => $ruc]);
    $this->tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['nombre' => 'LICENCIA DE FUNCIONAMIENTO RIESGO BAJO']));

    $this->expediente = fn (int $numero) => DB::table('expedientes')->insertGetId(Registros::expediente([
        'numero_expediente' => $numero, 'solicitante_id' => $this->titular, 'tipo_procedimiento_tupa_id' => $this->tupa,
        'fecha_recepcion' => CarbonImmutable::parse('2026-09-02 09:00', 'America/Lima')->utc()->toDateTimeString(),
    ]));
});

it('lista las licencias anuladas y revocadas del periodo con su sustento y quién las registró', function () {
    $lf = DB::table('licencias_funcionamiento')->insertGetId(Registros::licencia([
        'numero_licencia' => 345, 'nombre_comercial' => 'BODEGA LA SELVA', 'direccion' => 'JR. LOS PINOS 120',
        'fecha_emision' => '2025-03-10', 'expediente_id' => ($this->expediente)(700), 'titular_id' => $this->titular, 'conductor_id' => $this->titular,
    ]));
    $estado = fn (int $estadoId, string $fecha, string $documento) => DB::table('licencias_funcionamiento_estados')->insert([
        'fecha_estado' => $fecha, 'documento' => $documento, 'observaciones' => 'Cierre definitivo del local',
        'fecha_digitacion' => '2026-09-15 20:30:00+00', 'estado_id' => $estadoId, 'licencia_funcionamiento_id' => $lf,
        'usuario_id' => $this->registrador->id,
    ]);
    $estado(104, '2026-09-15', 'RG N° 045-2026-MDM/GDES'); // revocada en el periodo
    $estado(102, '2026-09-10', 'TRANSFERENCIA');           // estado activo: no es baja
    $estado(103, '2026-08-20', 'RG N° 030-2026');          // fuera del periodo

    $r = $this->actingAs($this->registrador, 'api')
        ->getJson(URL_REPORTES.'bajas/licencias/?desde=2026-09-01&hasta=2026-09-30')
        ->assertOk()
        ->json();

    expect($r['resumen'])->toBe([
        'total' => 1,
        'por_estado' => [['estado' => 'Anulada', 'total' => 0], ['estado' => 'Revocada', 'total' => 1]],
    ]);
    expect($r['registros'])->toBe([[
        'fecha_estado' => '2026-09-15',
        'estado' => 'Revocada',
        'documento' => 'RG N° 045-2026-MDM/GDES',
        'motivo' => 'Cierre definitivo del local',
        'numero' => 345,
        'numero_expediente' => 700,
        'nombre_comercial' => 'BODEGA LA SELVA',
        'titular' => 'COMERCIAL SELVA S.A.C.',
        'documento_titular' => '20123456789',
        'direccion' => 'JR. LOS PINOS 120',
        'fecha_emision' => '2025-03-10',
        'registrado_por' => 'ANA TORRES',
        'fecha_registro' => '2026-09-15 15:30', // 20:30 UTC = 15:30 en Perú
    ]]);
});

it('lista los certificados ITSE dados de baja sin mezclar licencias', function () {
    $itse = DB::table('itse')->insertGetId(Registros::itse([
        'numero_itse' => 88, 'fecha_expedicion' => '2025-06-01', 'expediente_id' => ($this->expediente)(701),
        'titular_id' => $this->titular, 'conductor_id' => $this->titular,
    ]));
    DB::table('itse_estados')->insert([
        'fecha_estado' => '2026-09-05', 'documento' => 'RG N° 050-2026', 'observaciones' => 'Riesgo no subsanado',
        'fecha_digitacion' => now(), 'estado_id' => 103, 'itse_id' => $itse, 'usuario_id' => $this->registrador->id,
    ]);

    $r = $this->actingAs($this->registrador, 'api')
        ->getJson(URL_REPORTES.'bajas/itse/?desde=2026-09-01&hasta=2026-09-30')
        ->assertOk()
        ->json();

    expect($r['resumen']['total'])->toBe(1)
        ->and($r['registros'][0])->toMatchArray(['estado' => 'Anulada', 'numero' => 88, 'fecha_emision' => '2025-06-01']);

    $this->actingAs($this->registrador, 'api')
        ->getJson(URL_REPORTES.'bajas/licencias/?desde=2026-09-01&hasta=2026-09-30')
        ->assertOk()
        ->assertJsonPath('resumen.total', 0);
});

it('lista los expedientes improcedentes de cada tipo por separado', function () {
    $exp = ($this->expediente)(702);
    $improcedente = fn (string $tipo, string $fecha) => DB::table('autorizaciones_improcedentes')->insert(Registros::autorizacionImprocedente([
        'tipo_autorizacion' => $tipo, 'fecha_rechazo' => $fecha, 'documento' => "RG {$tipo}", 'observaciones' => 'Zonificación no compatible',
        'expediente_id' => $exp, 'usuario_id' => $this->registrador->id, 'fecha_digitacion' => '2026-09-20 14:00:00+00',
    ]));
    $improcedente('LF', '2026-09-20');
    $improcedente('ITSE', '2026-09-21');
    $improcedente('LF', '2026-10-01'); // fuera del periodo

    $r = $this->actingAs($this->registrador, 'api')
        ->getJson(URL_REPORTES.'improcedentes/licencias/?desde=2026-09-01&hasta=2026-09-30')
        ->assertOk()
        ->json();

    expect($r['resumen'])->toBe(['total' => 1])
        ->and($r['registros'])->toBe([[
            'fecha_rechazo' => '2026-09-20',
            'documento' => 'RG LF',
            'motivo' => 'Zonificación no compatible',
            'numero_expediente' => 702,
            'procedimiento' => 'LICENCIA DE FUNCIONAMIENTO RIESGO BAJO',
            'solicitante' => 'COMERCIAL SELVA S.A.C.',
            'documento_solicitante' => '20123456789',
            'fecha_recepcion' => '2026-09-02',
            'registrado_por' => 'ANA TORRES',
            'fecha_registro' => '2026-09-20 09:00',
        ]]);

    $this->actingAs($this->registrador, 'api')
        ->getJson(URL_REPORTES.'improcedentes/itse/?desde=2026-09-01&hasta=2026-09-30')
        ->assertOk()
        ->assertJsonPath('resumen.total', 1)
        ->assertJsonPath('registros.0.documento', 'RG ITSE');
});

it('sin fechas usa el mes en curso', function () {
    CarbonImmutable::setTestNow('2026-10-28 12:00:00');

    $this->actingAs($this->registrador, 'api')
        ->getJson(URL_REPORTES.'improcedentes/licencias/')
        ->assertOk()
        ->assertJson(['desde' => '2026-10-01', 'hasta' => '2026-10-31']);

    CarbonImmutable::setTestNow();
});

it('cada reporte exige el acceso de su área', function () {
    $soloLicencias = ($this->personal)(['licencias' => true]);
    $soloItse = ($this->personal)(['itse' => true]);

    $this->getJson(URL_REPORTES.'bajas/licencias/')->assertUnauthorized();

    $this->actingAs($soloLicencias, 'api')->getJson(URL_REPORTES.'bajas/licencias/')->assertOk();
    $this->actingAs($soloLicencias, 'api')->getJson(URL_REPORTES.'improcedentes/licencias/')->assertOk();
    $this->actingAs($soloLicencias, 'api')->getJson(URL_REPORTES.'bajas/itse/')->assertForbidden();
    $this->actingAs($soloLicencias, 'api')->getJson(URL_REPORTES.'improcedentes/itse/')->assertForbidden();

    $this->actingAs($soloItse, 'api')->getJson(URL_REPORTES.'bajas/itse/')->assertOk();
    $this->actingAs($soloItse, 'api')->getJson(URL_REPORTES.'improcedentes/itse/')->assertOk();
    $this->actingAs($soloItse, 'api')->getJson(URL_REPORTES.'bajas/licencias/')->assertForbidden();
});

it('valida las fechas', function () {
    $this->actingAs($this->registrador, 'api')
        ->getJson(URL_REPORTES.'bajas/itse/?desde=2026-09-30&hasta=2026-09-01')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['hasta']);
});
