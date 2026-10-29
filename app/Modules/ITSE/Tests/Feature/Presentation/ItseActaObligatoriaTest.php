<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $id = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(['username' => 'oficina_itse', 'is_staff' => true]));
    DB::table('usuarios_perfiles')->insert([
        'user_id' => $id, 'expedientes' => false, 'licencias' => false, 'itse' => true, 'admin' => false,
        'es_inspector' => false, 'es_jefe_itse' => false, 'reportes_completos' => false,
        'fecha_digitacion' => now(), 'user_id_digitador' => $id,
    ]);
    $this->oficina = User::find($id);
    $this->inspector = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(['username' => 'inspector_itse']));

    $this->persona = DB::table('personas')->insertGetId(Registros::persona());
    $this->nivel = DB::table('niveles_riesgo')->insertGetId(Registros::nivelRiesgo());
    $conItse = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['requiere_itse' => true]));

    $this->expediente = fn (array $datos = []) => DB::table('expedientes')->insertGetId(Registros::expediente(array_merge([
        'numero_expediente' => 701, 'solicitante_id' => $this->persona, 'tipo_procedimiento_tupa_id' => $conItse,
        'usuario_id' => $id, 'fecha_recepcion' => '2026-10-20',
    ], $datos)));

    // Acta registrada por el inspector: visita 1 (Aprobado/Observado) o 2 (Aprobado/Desaprobado)
    $this->acta = function (int $expediente, string $resultado, int $visita = 1, array $datos = []): int {
        $asignacion = DB::table('inspecciones_asignaciones')->where('expediente_id', $expediente)->value('id')
            ?? DB::table('inspecciones_asignaciones')->insertGetId([
                'expediente_id' => $expediente, 'inspector_id' => $this->inspector, 'nombre_comercial' => 'BODEGA',
                'direccion' => 'JR. LIMA 1', 'asignado_por' => $this->oficina->id, 'fecha_asignacion' => now(),
            ]);

        return DB::table('inspecciones_actas')->insertGetId(array_merge([
            'expediente_id' => $expediente, 'asignacion_id' => $asignacion, 'inspector_id' => $this->inspector,
            'numero_acta' => 'ACTA-'.uniqid(), 'visita' => $visita, 'fecha_visita' => now()->subDays(3 - $visita),
            'area_m2' => 45.5, 'aforo' => 30, 'extintores' => 'CUMPLE', 'senalizacion' => 'CUMPLE',
            'luces_emergencia' => 'CUMPLE', 'tableros_electricos' => 'CUMPLE', 'resultado' => $resultado,
            'observaciones' => $resultado === 'APROBADO' ? null : 'Extintor vencido',
            'fecha_limite_subsanacion' => $resultado === 'OBSERVADO' ? now()->toDateString() : null,
            'fecha_registro' => now(),
        ], $datos));
    };

    $this->emitir = fn (int $expediente) => $this->actingAs($this->oficina, 'api')->postJson('/api/lf-itse/itse/', [
        'numero_itse' => random_int(1000, 999999), 'fecha_expedicion' => '2026-10-29', 'fecha_caducidad' => '2028-10-29',
        'nombre_comercial' => 'BODEGA', 'direccion' => 'JR. LIMA 1', 'resolucion_numero' => 'RES-1', 'area' => 45.5,
        'capacidad_aforo' => 30, 'se_puede_publicar' => false, 'numero_recibo_pago' => 'REC-'.uniqid(), 'tipo_itse_id' => 1,
        'expediente_id' => $expediente, 'nivel_riesgo_id' => $this->nivel, 'conductor_id' => $this->persona, 'titular_id' => $this->persona,
    ]);
    $this->situacion = fn (int $expediente) => $this->actingAs($this->oficina, 'api')
        ->getJson("/api/lf-itse/itse/expedientes/{$expediente}/inspeccion/");
});

it('no emite el certificado sin acta de inspección aprobada', function () {
    $exp = ($this->expediente)();

    ($this->situacion)($exp)->assertOk()->assertJson(['exige_acta' => true, 'se_puede_emitir' => false, 'acta' => null]);
    ($this->emitir)($exp)->assertUnprocessable()->assertJson([
        'error' => 'El expediente N° 701 aún no tiene acta de inspección Aprobada: el certificado ITSE se emite después de que el inspector registre el acta de su visita.',
    ]);
    $this->actingAs($this->oficina, 'api')->getJson('/api/lf-itse/itse/verificar-expediente/?numero_expediente=701&anio=2026')
        ->assertOk()->assertJson(['se_puede_emitir_itse' => false, 'expediente_id' => $exp]);

    // Observada: falta la reinspección
    ($this->acta)($exp, 'OBSERVADO');
    ($this->emitir)($exp)->assertUnprocessable()->assertJsonPath('error', fn (string $e) => str_contains($e, 'está Observada'));

    // Desaprobada en la reinspección: corresponde la denegatoria
    ($this->acta)($exp, 'DESAPROBADO', 2);
    ($this->emitir)($exp)->assertUnprocessable()->assertJsonPath('error', fn (string $e) => str_contains($e, 'registrar la denegatoria'));
    expect(DB::table('itse')->count())->toBe(0);
});

it('emite el certificado con el acta aprobada y la informa para completar el formulario', function () {
    $exp = ($this->expediente)();
    ($this->acta)($exp, 'OBSERVADO');
    $aprobada = ($this->acta)($exp, 'APROBADO', 2, ['numero_acta' => 'ACTA-777', 'area_m2' => 60.25, 'aforo' => 42]);

    ($this->situacion)($exp)->assertOk()->assertJson([
        'numero_expediente' => 701, 'exige_acta' => true, 'se_puede_emitir' => true, 'mensaje' => null,
        'acta' => ['id' => $aprobada, 'numero_acta' => 'ACTA-777', 'resultado' => 'APROBADO', 'area_m2' => 60.25, 'aforo' => 42],
    ]);
    ($this->emitir)($exp)->assertCreated();
});

it('un acta aprobada pero anulada no sustenta el certificado', function () {
    $exp = ($this->expediente)();
    ($this->acta)($exp, 'APROBADO', 1, [
        'fecha_anulacion' => now(), 'motivo_anulacion' => 'Metraje equivocado', 'anulado_por' => $this->oficina->id,
    ]);

    ($this->situacion)($exp)->assertJson(['se_puede_emitir' => false, 'acta' => null]);
    ($this->emitir)($exp)->assertUnprocessable();
});

it('no exige acta si el procedimiento no requiere ITSE ni a expedientes recibidos antes de la fecha configurada', function () {
    $sinItse = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['requiere_itse' => false]));
    $exp = ($this->expediente)(['tipo_procedimiento_tupa_id' => $sinItse]);
    ($this->situacion)($exp)->assertJson(['exige_acta' => false, 'se_puede_emitir' => true]);
    ($this->emitir)($exp)->assertCreated();

    config(['inspecciones.acta_obligatoria_desde' => '2026-11-01']);
    $antiguo = ($this->expediente)(['numero_expediente' => 702, 'fecha_recepcion' => '2026-10-31']);
    $nuevo = ($this->expediente)(['numero_expediente' => 703, 'fecha_recepcion' => '2026-11-01']);
    ($this->situacion)($antiguo)->assertJson(['exige_acta' => false, 'se_puede_emitir' => true]);
    ($this->emitir)($antiguo)->assertCreated();
    ($this->situacion)($nuevo)->assertJson(['exige_acta' => true, 'se_puede_emitir' => false]);
    ($this->emitir)($nuevo)->assertUnprocessable();
});

it('la consulta de inspección es para la oficina de ITSE', function () {
    $exp = ($this->expediente)();
    $this->actingAs(User::find($this->inspector), 'api')->getJson("/api/lf-itse/itse/expedientes/{$exp}/inspeccion/")->assertForbidden();
    ($this->situacion)(999999)->assertNotFound();
});
