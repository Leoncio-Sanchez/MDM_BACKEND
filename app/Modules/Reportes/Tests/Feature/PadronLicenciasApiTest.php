<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;

uses(Tests\TestCase::class, RefreshDatabase::class);

const URL_PADRON = '/api/lf-itse/reportes/licencias/';

beforeEach(function () {
    /** Personal con los accesos indicados */
    $this->personal = function (array $accesos): User {
        $id = DB::table('auth_user')->insertGetId(\Tests\Fixtures\UsuarioAuth::datos(['username' => 'p_'.uniqid(), 'is_staff' => true]));
        DB::table('usuarios_perfiles')->insert(array_merge(
            ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false],
            $accesos,
            ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
        ));

        return User::find($id);
    };

    // Catálogos
    foreach (['RIESGO BAJO', 'RIESGO MEDIO', 'RIESGO ALTO', 'RIESGO MUY ALTO'] as $i => $nombre) {
        DB::table('niveles_riesgo')->insert(Registros::nivelRiesgo(['id' => 101 + $i, 'nombre' => $nombre, 'codigo' => 'N'.$i]));
    }
    $this->zona = DB::table('zonificaciones')->insertGetId(Registros::zonificacion(['nombre' => 'COMERCIO ZONAL']));
    $this->tipo = DB::table('tipos_licencia')->insertGetId(Registros::tipoLicencia(['nombre' => 'INDETERMINADA']));
    $this->tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['plazo_atencion_dias' => 5]));
    $ruc = DB::table('tipos_documento_identidad')->insertGetId(Registros::tipoDocumentoIdentidad(['codigo' => '06', 'nombre' => 'RUC']));
    $dni = DB::table('tipos_documento_identidad')->insertGetId(Registros::tipoDocumentoIdentidad(['codigo' => '01', 'nombre' => 'DNI']));

    // Titular con DNI y RUC: el informe muestra el RUC
    $this->titular = DB::table('personas')->insertGetId(Registros::persona([
        'nombres' => 'ROSA', 'apellido_paterno' => 'QUISPE', 'apellido_materno' => 'HUAMAN',
    ]));
    DB::table('personas_documentos')->insert([
        ['numero_documento' => '45678912', 'persona_id' => $this->titular, 'tipo_documento_identidad_id' => $dni],
        ['numero_documento' => '10456789123', 'persona_id' => $this->titular, 'tipo_documento_identidad_id' => $ruc],
    ]);
    $this->giro = DB::table('giros')->insertGetId(Registros::giro(['nombre' => 'BODEGA']));

    /** Licencia emitida con su expediente; recepción en hora de Perú (se guarda en UTC) */
    $this->licencia = function (int $numero, string $recepcionLima, string $emision, int $nivel) {
        $exp = DB::table('expedientes')->insertGetId(Registros::expediente([
            'numero_expediente' => 900 + $numero, 'tipo_procedimiento_tupa_id' => $this->tupa,
            'fecha_recepcion' => CarbonImmutable::parse($recepcionLima, 'America/Lima')->utc()->toDateTimeString(),
        ]));
        $id = DB::table('licencias_funcionamiento')->insertGetId(Registros::licencia([
            'numero_licencia' => $numero, 'nombre_comercial' => "NEGOCIO {$numero}", 'fecha_emision' => $emision,
            'expediente_id' => $exp, 'titular_id' => $this->titular, 'conductor_id' => $this->titular,
            'zonificacion_id' => $this->zona, 'tipo_licencia_id' => $this->tipo, 'nivel_riesgo_id' => $nivel,
        ]));
        DB::table('licencias_funcionamiento_giros')->insert([
            'licencia_funcionamiento_id' => $id, 'giro_id' => $this->giro, 'fecha_digitacion' => now(), 'usuario_id' => 1,
        ]);
    };
});

it('arma el padrón y el resumen con el tiempo promedio y el cumplimiento del plazo TUPA', function () {
    ($this->licencia)(1, '2026-10-05 10:00', '2026-10-08', 101); // lun → jue: 3 días hábiles (plazo 5: dentro)
    ($this->licencia)(2, '2026-10-01 10:00', '2026-10-15', 103); // jue → jue: 10 días hábiles (fuera de plazo)
    ($this->licencia)(3, '2026-09-01 10:00', '2026-09-20', 101); // emitida fuera del rango

    $r = $this->actingAs(($this->personal)(['licencias' => true]), 'api')
        ->getJson(URL_PADRON.'?desde=2026-10-01&hasta=2026-10-31')
        ->assertOk()
        ->json();

    expect($r['resumen'])->toBe([
        'total' => 2,
        'por_nivel_riesgo' => [
            ['nivel' => 'RIESGO BAJO', 'total' => 1],
            ['nivel' => 'RIESGO MEDIO', 'total' => 0],
            ['nivel' => 'RIESGO ALTO', 'total' => 1],
            ['nivel' => 'RIESGO MUY ALTO', 'total' => 0],
        ],
        'tiempo_promedio_dias_habiles' => 6.5,
        'dentro_de_plazo' => 1,
        'porcentaje_cumplimiento' => 50,
    ]);

    expect($r['licencias'][0])->toMatchArray([
        'numero_licencia' => 1,
        'numero_expediente' => 901,
        'nombre_comercial' => 'NEGOCIO 1',
        'titular' => 'ROSA QUISPE HUAMAN',
        'documento_titular' => '10456789123',
        'giros' => 'BODEGA',
        'zonificacion' => 'COMERCIO ZONAL',
        'nivel_riesgo' => 'RIESGO BAJO',
        'tipo_licencia' => 'INDETERMINADA',
        'fecha_recepcion' => '2026-10-05',
        'fecha_emision' => '2026-10-08',
        'dias_habiles' => 3,
        'plazo_dias' => 5,
        'dentro_de_plazo' => true,
    ]);
    expect($r['licencias'][1]['dias_habiles'])->toBe(10)
        ->and($r['licencias'][1]['dentro_de_plazo'])->toBeFalse();
});

it('sin fechas usa el mes en curso y sin licencias no inventa promedios', function () {
    CarbonImmutable::setTestNow('2026-10-28 12:00:00');
    \Illuminate\Support\Carbon::setTestNow('2026-10-28 12:00:00');

    $r = $this->actingAs(($this->personal)(['licencias' => true]), 'api')->getJson(URL_PADRON)->assertOk()->json();

    expect([$r['desde'], $r['hasta']])->toBe(['2026-10-01', '2026-10-31'])
        ->and($r['resumen']['total'])->toBe(0)
        ->and($r['resumen']['tiempo_promedio_dias_habiles'])->toBeNull()
        ->and($r['resumen']['porcentaje_cumplimiento'])->toBeNull();

    CarbonImmutable::setTestNow();
    \Illuminate\Support\Carbon::setTestNow();
});

it('solo lo ve quien tiene el acceso de Licencias', function () {
    $this->getJson(URL_PADRON)->assertUnauthorized();
    $this->actingAs(($this->personal)(['admin' => true, 'itse' => true]), 'api')->getJson(URL_PADRON)->assertForbidden();
    $this->actingAs(($this->personal)(['licencias' => true]), 'api')->getJson(URL_PADRON)->assertOk();
});

it('valida las fechas', function () {
    $this->actingAs(($this->personal)(['licencias' => true]), 'api')
        ->getJson(URL_PADRON.'?desde=2026-10-31&hasta=2026-10-01')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['hasta']);
});
