<?php

declare(strict_types=1);

use App\Modules\ConsultaPublica\Infrastructure\Providers\ConsultaPublicaServiceProvider;
use App\Shared\Domain\Calendario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Fixtures\Registros;

uses(Tests\TestCase::class, RefreshDatabase::class);

const URL_CONSULTA = '/api/publico/consulta/';

beforeEach(function () {
    RateLimiter::clear('consulta-publica');

    /** Persona con un documento; devuelve su id */
    $this->persona = function (string $documento): int {
        $id = DB::table('personas')->insertGetId(Registros::persona(['nombres' => 'TITULAR '.$documento]));
        DB::table('personas_documentos')->insert([
            'numero_documento' => $documento, 'persona_id' => $id, 'tipo_documento_identidad_id' => 1,
        ]);

        return $id;
    };
    $this->consultar = fn (string $tipo, int $numero, string $documento) => $this->getJson(
        URL_CONSULTA.'?'.http_build_query(['tipo' => $tipo, 'numero' => $numero, 'documento' => $documento])
    );
});

it('no requiere iniciar sesión y muestra solo datos públicos de la licencia', function () {
    $titular = ($this->persona)('12345678');
    $licencia = DB::table('licencias_funcionamiento')->insertGetId(Registros::licencia([
        'numero_licencia' => 501, 'nombre_comercial' => 'BODEGA LUCHITO', 'titular_id' => $titular,
        'direccion' => 'JR. PRIVADO 123', 'fecha_emision' => '2026-01-15',
    ]));
    $giro = DB::table('giros')->insertGetId(Registros::giro(['nombre' => 'VENTA DE ABARROTES']));
    DB::table('licencias_funcionamiento_giros')->insert([
        'licencia_funcionamiento_id' => $licencia, 'giro_id' => $giro, 'fecha_digitacion' => now(), 'usuario_id' => 1,
    ]);

    $respuesta = ($this->consultar)('licencia', 501, '12345678')->assertOk();

    expect($respuesta->json())->toBe([[
        'tipo' => 'licencia',
        'numero' => 501,
        'estado' => 'VIGENTE',
        'nombre_comercial' => 'BODEGA LUCHITO',
        'procedimiento' => null,
        'giros' => ['VENTA DE ABARROTES'],
        'fecha_emision' => '2026-01-15',
        'fecha_vencimiento' => null,
        'vigencia_indeterminada' => true,
    ]]);
    // Nada de datos personales
    expect($respuesta->getContent())->not->toContain('JR. PRIVADO')->not->toContain('TITULAR')->not->toContain('12345678');
});

it('responde lo mismo si el número no existe o si el documento no corresponde', function () {
    $titular = ($this->persona)('12345678');
    ($this->persona)('87654321');
    DB::table('licencias_funcionamiento')->insert(Registros::licencia(['numero_licencia' => 502, 'titular_id' => $titular]));

    $documentoAjeno = ($this->consultar)('licencia', 502, '87654321')->assertNotFound()->json();
    $numeroInexistente = ($this->consultar)('licencia', 999, '12345678')->assertNotFound()->json();

    expect($documentoAjeno)->toBe($numeroInexistente)
        ->and($documentoAjeno['error'])->toContain('No encontramos un trámite');
});

it('el conductor también puede consultar su licencia', function () {
    $titular = ($this->persona)('20123456789');
    $conductor = ($this->persona)('44556677');
    DB::table('licencias_funcionamiento')->insert(Registros::licencia([
        'numero_licencia' => 503, 'titular_id' => $titular, 'conductor_id' => $conductor,
    ]));

    ($this->consultar)('licencia', 503, '44556677')->assertOk()->assertJsonPath('0.numero', 503);
});

it('muestra un ITSE vencido o inactivado', function () {
    $titular = ($this->persona)('12345678');
    DB::table('itse')->insert(Registros::itse([
        'numero_itse' => 601, 'titular_id' => $titular,
        'fecha_expedicion' => Calendario::hoy()->subYears(2)->subDay()->toDateString(),
        'fecha_caducidad' => Calendario::hoy()->subDay()->toDateString(),
    ]));
    $inactivo = DB::table('itse')->insertGetId(Registros::itse(['numero_itse' => 602, 'titular_id' => $titular]));
    $clausura = DB::table('estados')->insertGetId(Registros::estado(['nombre' => 'CLAUSURA', 'esta_activo' => false]));
    DB::table('itse_estados')->insert([
        'itse_id' => $inactivo, 'estado_id' => $clausura, 'fecha_estado' => now(), 'documento' => 'RES',
        'observaciones' => '', 'fecha_digitacion' => now(), 'usuario_id' => 1,
    ]);

    ($this->consultar)('itse', 601, '12345678')->assertOk()->assertJsonPath('0.estado', 'VENCIDO');
    ($this->consultar)('itse', 602, '12345678')->assertOk()->assertJsonPath('0.estado', 'INACTIVO');
});

it('muestra el estado del expediente y su procedimiento, también al representante', function () {
    $solicitante = ($this->persona)('12345678');
    $representante = ($this->persona)('11223344');
    $tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa([
        'nombre' => 'LICENCIA DE FUNCIONAMIENTO', 'requiere_lf' => true,
    ]));
    $expediente = DB::table('expedientes')->insertGetId(Registros::expediente([
        'numero_expediente' => 701, 'solicitante_id' => $solicitante, 'representante_id' => $representante,
        'tipo_procedimiento_tupa_id' => $tupa,
    ]));

    ($this->consultar)('expediente', 701, '12345678')->assertOk()
        ->assertJsonPath('0.estado', 'EN TRÁMITE')
        ->assertJsonPath('0.procedimiento', 'LICENCIA DE FUNCIONAMIENTO');

    DB::table('licencias_funcionamiento')->insert(Registros::licencia(['expediente_id' => $expediente]));

    ($this->consultar)('expediente', 701, '11223344')->assertOk()->assertJsonPath('0.estado', 'ATENDIDO');
});

it('valida los datos con mensajes claros', function () {
    $this->getJson(URL_CONSULTA.'?tipo=multa&numero=abc&documento=12')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['tipo', 'numero', 'documento']);
});

it('limita las consultas seguidas desde una misma IP', function () {
    for ($i = 0; $i < ConsultaPublicaServiceProvider::CONSULTAS_POR_MINUTO; $i++) {
        ($this->consultar)('licencia', 1, '12345678')->assertNotFound();
    }

    ($this->consultar)('licencia', 1, '12345678')
        ->assertStatus(429)
        ->assertJsonPath('error', 'Realizó demasiadas consultas seguidas. Espere un minuto e intente nuevamente.');
});
