<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

const URL_ACTAS = '/api/lf-itse/inspecciones/';
// Lunes: con 3 días hábiles, la subsanación vence el jueves 29
const PRIMERA_VISITA = '2026-10-26T10:00:00-05:00';

beforeEach(function () {
    Storage::fake('media');

    $this->usuario = function (array $perfil, array $datos = []): User {
        $id = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(array_merge(['username' => 'u_'.uniqid(), 'is_staff' => true], $datos)));
        DB::table('usuarios_perfiles')->insert(array_merge(
            ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false,
                'es_inspector' => false, 'es_jefe_itse' => false, 'reportes_completos' => false],
            $perfil,
            ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
        ));

        return User::find($id);
    };
    $this->jefe = ($this->usuario)(['itse' => true, 'es_jefe_itse' => true]);
    $this->oficina = ($this->usuario)(['itse' => true]);
    $this->carlos = ($this->usuario)(['es_inspector' => true], ['first_name' => 'CARLOS', 'is_staff' => false]);
    $this->maria = ($this->usuario)(['es_inspector' => true], ['first_name' => 'MARIA', 'is_staff' => false]);

    $tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['requiere_itse' => true]));
    $persona = DB::table('personas')->insertGetId(Registros::persona());
    $this->expediente = DB::table('expedientes')->insertGetId(Registros::expediente([
        'numero_expediente' => 801, 'solicitante_id' => $persona, 'tipo_procedimiento_tupa_id' => $tupa,
    ]));

    $this->asignacion = $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS.'asignaciones/', [
        'expediente_id' => $this->expediente, 'inspector_id' => $this->carlos->id,
        'nombre_comercial' => 'BODEGA', 'direccion' => 'JR. LIMA 1',
    ])->assertCreated()->json('id');

    // Acta completa y conforme; cada test cambia lo que necesita
    $this->acta = fn (array $cambios = []) => array_merge([
        'numero_acta' => 'acta-001-2026',
        'fecha_visita' => PRIMERA_VISITA,
        'area_m2' => '45.5',
        'aforo' => 30,
        'extintores' => 'CUMPLE',
        'senalizacion' => 'CUMPLE',
        'luces_emergencia' => 'CUMPLE',
        'tableros_electricos' => 'CUMPLE',
        'fotos' => [
            ['tipo' => 'FACHADA', 'archivo' => UploadedFile::fake()->image('fachada.jpg')],
            ['tipo' => 'EXTINTORES', 'archivo' => UploadedFile::fake()->image('extintor.jpg')],
            ['tipo' => 'TABLEROS', 'archivo' => UploadedFile::fake()->image('tablero.png')],
        ],
    ], $cambios);
    $this->registrar = fn (User $quien, array $datos, ?int $asignacion = null) => $this->actingAs($quien, 'api')
        ->post(URL_ACTAS.'mi-bandeja/'.($asignacion ?? $this->asignacion).'/acta/', $datos, ['Accept' => 'application/json']);
});

it('registra un acta conforme: queda aprobada con sus fotos y sale de la bandeja', function () {
    $acta = ($this->registrar)($this->carlos, ($this->acta)())
        ->assertCreated()
        ->assertJson([
            'numero_expediente' => 801, 'numero_acta' => 'ACTA-001-2026', 'visita' => 1,
            'area_m2' => 45.5, 'aforo' => 30, 'resultado' => 'APROBADO', 'fecha_limite_subsanacion' => null,
            'inspector_nombre' => 'CARLOS',
        ])
        ->json();

    expect(array_column($acta['fotos'], 'tipo'))->toBe(['FACHADA', 'EXTINTORES', 'TABLEROS']);
    expect(Storage::disk('media')->allFiles("inspecciones/actas/{$acta['id']}"))->toHaveCount(3);

    $this->actingAs($this->carlos, 'api')->getJson(URL_ACTAS.'mi-bandeja/')->assertExactJson([]);

    // La oficina lo sigue viendo: falta emitir el certificado
    $fila = $this->actingAs($this->jefe, 'api')->getJson(URL_ACTAS.'por-inspeccionar/')->json()[0];
    expect($fila)->toMatchArray(['ultima_acta_id' => $acta['id'], 'ultima_acta_resultado' => 'APROBADO']);

    // Con dictamen final ya no se reasigna ni se registra otra acta
    $this->actingAs($this->jefe, 'api')
        ->postJson(URL_ACTAS."asignaciones/{$this->asignacion}/reasignar/", ['inspector_id' => $this->maria->id, 'motivo' => 'Cambio de inspector'])
        ->assertStatus(422);
    ($this->registrar)($this->carlos, ($this->acta)(['numero_acta' => 'ACTA-002-2026', 'fecha_visita' => now()->toIso8601String()]))
        ->assertStatus(422)
        ->assertJson(['error' => 'El expediente N° 801 ya tiene dictamen final: no admite más actas.']);
});

it('observa en la 1.ª visita con plazo en días hábiles y desaprueba si la reinspección no encuentra la corrección', function () {
    ($this->registrar)($this->carlos, ($this->acta)(['extintores' => 'NO_CUMPLE']))
        ->assertStatus(422)
        ->assertJson(['error' => 'Detalle las observaciones encontradas: hay puntos que no cumplen.']);
    ($this->registrar)($this->carlos, ($this->acta)(['extintores' => 'NO_CUMPLE', 'observaciones' => 'Extintor vencido']))
        ->assertStatus(422)
        ->assertJson(['error' => 'Indique el plazo de subsanación (2 o 3 días hábiles).']);

    ($this->registrar)($this->carlos, ($this->acta)([
        'extintores' => 'NO_CUMPLE', 'observaciones' => 'Extintor vencido', 'plazo_subsanacion_dias' => 3,
    ]))->assertCreated()->assertJson(['resultado' => 'OBSERVADO', 'visita' => 1, 'fecha_limite_subsanacion' => '2026-10-29']);

    // Sigue en la bandeja, ahora como reinspección con las observaciones a verificar
    expect($this->actingAs($this->carlos, 'api')->getJson(URL_ACTAS.'mi-bandeja/')->json()[0])->toMatchArray([
        'ultima_acta_resultado' => 'OBSERVADO', 'ultima_acta_visita' => 1,
        'fecha_limite_subsanacion' => '2026-10-29', 'ultima_acta_observaciones' => 'Extintor vencido',
    ]);

    // La reinspección no puede ser anterior a la 1.ª visita
    ($this->registrar)($this->carlos, ($this->acta)(['numero_acta' => 'ACTA-002', 'fecha_visita' => '2026-10-25T10:00:00-05:00']))
        ->assertStatus(422);

    ($this->registrar)($this->carlos, ($this->acta)([
        'numero_acta' => 'ACTA-002', 'fecha_visita' => now()->subMinute()->toIso8601String(),
        'extintores' => 'NO_CUMPLE', 'observaciones' => 'No cambió el extintor',
    ]))->assertCreated()->assertJson(['resultado' => 'DESAPROBADO', 'visita' => 2, 'fecha_limite_subsanacion' => null]);

    $this->actingAs($this->carlos, 'api')->getJson(URL_ACTAS.'mi-bandeja/')->assertExactJson([]);
    expect(array_column($this->actingAs($this->oficina, 'api')->getJson(URL_ACTAS."expedientes/{$this->expediente}/actas/")->assertOk()->json(), 'resultado'))
        ->toBe(['OBSERVADO', 'DESAPROBADO']);
});

it('aprueba en la reinspección si se corrigió', function () {
    ($this->registrar)($this->carlos, ($this->acta)(['tableros_electricos' => 'NO_CUMPLE', 'observaciones' => 'Tablero sin tapa', 'plazo_subsanacion_dias' => 2]))
        ->assertCreated()->assertJson(['fecha_limite_subsanacion' => '2026-10-28']);

    ($this->registrar)($this->carlos, ($this->acta)(['numero_acta' => 'ACTA-002', 'fecha_visita' => now()->subMinute()->toIso8601String()]))
        ->assertCreated()->assertJson(['resultado' => 'APROBADO', 'visita' => 2]);
});

it('exige las fotos de sustento según lo que aplica al local', function () {
    $sinExtintores = ($this->acta)();
    unset($sinExtintores['fotos'][1]);
    ($this->registrar)($this->carlos, $sinExtintores)
        ->assertStatus(422)
        ->assertJson(['error' => 'Falta la foto de los extintores: es obligatoria como sustento del acta.']);

    // Si el grupo no aplica, su foto tampoco
    ($this->registrar)($this->carlos, array_merge($sinExtintores, ['extintores' => 'NO_APLICA']))->assertCreated();
    expect(Storage::disk('media')->allFiles('inspecciones/actas'))->toHaveCount(2);
});

it('valida número, fecha, metraje y formato de las fotos', function () {
    ($this->registrar)($this->carlos, ($this->acta)(['fecha_visita' => now()->addDay()->toIso8601String()]))
        ->assertStatus(422)
        ->assertJson(['error' => 'La fecha y hora de la visita no puede ser posterior al momento actual.']);

    ($this->registrar)($this->carlos, ($this->acta)(['area_m2' => 0, 'fotos' => [['tipo' => 'FACHADA', 'archivo' => UploadedFile::fake()->create('acta.pdf', 10, 'application/pdf')]]]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['area_m2', 'fotos.0.archivo']);

    // El número de acta no se repite, sin importar mayúsculas
    $otraAsignacion = function () {
        $exp = DB::table('expedientes')->insertGetId(Registros::expediente([
            'numero_expediente' => 802, 'solicitante_id' => DB::table('personas')->value('id'),
            'tipo_procedimiento_tupa_id' => DB::table('tipos_procedimiento_tupa')->value('id'),
        ]));

        return $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS.'asignaciones/', [
            'expediente_id' => $exp, 'inspector_id' => $this->carlos->id, 'nombre_comercial' => 'X', 'direccion' => 'Y',
        ])->json('id');
    };
    ($this->registrar)($this->carlos, ($this->acta)())->assertCreated();
    ($this->registrar)($this->carlos, ($this->acta)(['numero_acta' => 'ACTA-001-2026']), $otraAsignacion())
        ->assertStatus(409)
        ->assertJson(['error' => 'Ya existe un acta con el número ACTA-001-2026.']);
});

it('solo el inspector con la asignación vigente registra el acta', function () {
    ($this->registrar)($this->maria, ($this->acta)())->assertNotFound();
    ($this->registrar)($this->jefe, ($this->acta)())->assertForbidden();

    $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS."asignaciones/{$this->asignacion}/reasignar/", [
        'inspector_id' => $this->maria->id, 'motivo' => 'Inspector con descanso médico',
    ])->assertCreated();
    ($this->registrar)($this->carlos, ($this->acta)())->assertNotFound();
});

it('muestra el acta y sus fotos a la oficina y al inspector a cargo, a nadie más', function () {
    $acta = ($this->registrar)($this->carlos, ($this->acta)())->json();
    $foto = $acta['fotos'][0]['id'];

    $this->actingAs($this->oficina, 'api')->getJson(URL_ACTAS."actas/{$acta['id']}/")->assertOk()->assertJsonPath('numero_acta', 'ACTA-001-2026');
    $this->actingAs($this->oficina, 'api')->get(URL_ACTAS."actas/{$acta['id']}/fotos/{$foto}/")
        ->assertOk()->assertHeader('Content-Type', 'image/jpeg')->assertHeader('X-Content-Type-Options', 'nosniff');

    $this->actingAs($this->carlos, 'api')->getJson(URL_ACTAS."mi-bandeja/actas/{$acta['id']}/")->assertOk();
    $this->actingAs($this->carlos, 'api')->get(URL_ACTAS."mi-bandeja/actas/{$acta['id']}/fotos/{$foto}/")->assertOk();

    $this->actingAs($this->maria, 'api')->getJson(URL_ACTAS."mi-bandeja/actas/{$acta['id']}/")->assertNotFound();
    $this->actingAs($this->maria, 'api')->get(URL_ACTAS."mi-bandeja/actas/{$acta['id']}/fotos/{$foto}/", ['Accept' => 'application/json'])->assertNotFound();
    $this->actingAs($this->carlos, 'api')->getJson(URL_ACTAS."actas/{$acta['id']}/")->assertForbidden();
    $this->actingAs(($this->usuario)(['licencias' => true]), 'api')->getJson(URL_ACTAS."actas/{$acta['id']}/")->assertForbidden();
});

// ── Anulación ─────────────────────────────────────────────────────────────

it('el jefe anula un acta con error: queda como constancia y el inspector vuelve a registrar la visita', function () {
    $acta = ($this->registrar)($this->carlos, ($this->acta)())->assertCreated()->json();

    $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS."actas/{$acta['id']}/anular/", ['motivo' => '   corto   '])
        ->assertUnprocessable()->assertJsonValidationErrors('motivo');

    $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS."actas/{$acta['id']}/anular/", [
        'motivo' => 'Se registró un metraje equivocado', 'documento' => ' MEMO-015-2026 ',
    ])->assertOk()->assertJson([
        'anulada' => true, 'motivo_anulacion' => 'Se registró un metraje equivocado',
        'documento_anulacion' => 'MEMO-015-2026', 'resultado' => 'APROBADO',
    ]);

    // Vuelve a la bandeja avisando la anulación; las fotos se conservan
    expect($this->actingAs($this->carlos, 'api')->getJson(URL_ACTAS.'mi-bandeja/')->json()[0])->toMatchArray([
        'ultima_acta_id' => null, 'acta_anulada_numero' => 'ACTA-001-2026', 'acta_anulada_motivo' => 'Se registró un metraje equivocado',
    ]);
    expect(Storage::disk('media')->allFiles("inspecciones/actas/{$acta['id']}"))->toHaveCount(3);

    // Se registra otra vez la 1.ª visita, incluso con el mismo número del talonario
    ($this->registrar)($this->carlos, ($this->acta)(['area_m2' => '54.5']))
        ->assertCreated()->assertJson(['visita' => 1, 'numero_acta' => 'ACTA-001-2026', 'anulada' => false]);
    $this->actingAs($this->carlos, 'api')->getJson(URL_ACTAS.'mi-bandeja/')->assertExactJson([]);

    $actas = $this->actingAs($this->oficina, 'api')->getJson(URL_ACTAS."expedientes/{$this->expediente}/actas/")->json();
    expect(array_column($actas, 'anulada'))->toBe([true, false]);
    expect($actas[0]['anulado_por'])->not->toBeNull();

    $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS."actas/{$acta['id']}/anular/", ['motivo' => 'Otra vez el mismo motivo'])
        ->assertStatus(422)->assertJson(['error' => 'El acta N° ACTA-001-2026 ya está anulada.']);
});

it('anular la reinspección devuelve el expediente a observado; la 1.ª visita no se anula mientras haya reinspección', function () {
    $primera = ($this->registrar)($this->carlos, ($this->acta)([
        'extintores' => 'NO_CUMPLE', 'observaciones' => 'Extintor vencido', 'plazo_subsanacion_dias' => 3,
    ]))->json();
    $segunda = ($this->registrar)($this->carlos, ($this->acta)([
        'numero_acta' => 'ACTA-002', 'fecha_visita' => now()->subMinute()->toIso8601String(),
        'extintores' => 'NO_CUMPLE', 'observaciones' => 'No cambió el extintor',
    ]))->assertJson(['resultado' => 'DESAPROBADO'])->json();

    $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS."actas/{$primera['id']}/anular/", ['motivo' => 'Número de acta equivocado'])
        ->assertStatus(422)->assertJsonPath('error', fn (string $e) => str_contains($e, 'anule primero la reinspección'));

    $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS."actas/{$segunda['id']}/anular/", ['motivo' => 'Se marcó mal el checklist'])->assertOk();

    expect($this->actingAs($this->carlos, 'api')->getJson(URL_ACTAS.'mi-bandeja/')->json()[0])->toMatchArray([
        'ultima_acta_id' => $primera['id'], 'ultima_acta_resultado' => 'OBSERVADO', 'acta_anulada_numero' => 'ACTA-002',
    ]);
    ($this->registrar)($this->carlos, ($this->acta)(['numero_acta' => 'ACTA-003', 'fecha_visita' => now()->subMinute()->toIso8601String()]))
        ->assertCreated()->assertJson(['visita' => 2, 'resultado' => 'APROBADO']);
});

it('no se anula un acta si el expediente ya tiene denegatoria o certificado, ni lo hace quien no es jefe', function () {
    $acta = ($this->registrar)($this->carlos, ($this->acta)())->json();

    $this->actingAs($this->oficina, 'api')->postJson(URL_ACTAS."actas/{$acta['id']}/anular/", ['motivo' => 'Se registró con error'])->assertForbidden();
    $this->actingAs($this->carlos, 'api')->postJson(URL_ACTAS."actas/{$acta['id']}/anular/", ['motivo' => 'Se registró con error'])->assertForbidden();
    $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS.'actas/999999/anular/', ['motivo' => 'Se registró con error'])->assertNotFound();

    DB::table('autorizaciones_improcedentes')->insert(Registros::autorizacionImprocedente([
        'tipo_autorizacion' => 'ITSE', 'expediente_id' => $this->expediente, 'usuario_id' => $this->jefe->id,
    ]));
    $this->actingAs($this->jefe, 'api')->postJson(URL_ACTAS."actas/{$acta['id']}/anular/", ['motivo' => 'Se registró con error'])
        ->assertStatus(422)->assertJsonPath('error', fn (string $e) => str_contains($e, 'ya tiene certificado o denegatoria'));
    expect(DB::table('inspecciones_actas')->whereNotNull('fecha_anulacion')->count())->toBe(0);
});
