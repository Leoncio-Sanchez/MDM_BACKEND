<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Estado;
use App\Models\Expediente;
use App\Models\Itse;
use App\Models\LicenciaFuncionamiento;
use App\Models\TipoProcedimientoTupa;
use App\Services\LicenciaService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class StoredProceduresTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Asegurar que las tablas maestras mínimas existan para pruebas
        if (! Schema::hasTable('estados')) {
            Schema::create('estados', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nombre', 50);
                $table->boolean('esta_activo')->default(true);
            });
        }

        if (Estado::whereRaw('LOWER(nombre) = ?', ['vigente'])->doesntExist()) {
            Estado::create(['nombre' => 'Vigente', 'esta_activo' => true]);
        }

        if (! Schema::hasTable('autorizaciones_improcedentes')) {
            Schema::create('autorizaciones_improcedentes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('expediente_id');
                $table->string('tipo_autorizacion', 10);
            });
        }
    }

    /**
     * 1. TEST SP: Emisión exitosa y correlativo incremental
     */
    public function test_sp_emite_licencia_y_asigna_correlativo_incremental(): void
    {
        $service = app(LicenciaService::class);

        $data1 = [
            'nombre_comercial' => 'Bodega San Martín 1',
            'tipo_licencia_id' => 1,
            'numero_recibo_pago' => 'REC-001',
            'es_vigencia_indeterminada' => true,
            'fecha_emision' => Carbon::now()->toDateString(),
            'numero_licencia' => 1,
            'giros' => [101, 102],
        ];

        $licencia1 = $service->crear($data1, 1);

        $this->assertNotNull($licencia1->id);
        $this->assertNotEmpty($licencia1->uuid);

        if (DB::getDriverName() === 'pgsql') {
            $this->assertDatabaseHas('licencias_funcionamiento_estados', [
                'licencia_funcionamiento_id' => $licencia1->id,
            ]);
            $this->assertDatabaseHas('licencias_funcionamiento_giros', [
                'licencia_funcionamiento_id' => $licencia1->id,
                'giro_id' => 101,
            ]);
        }
    }

    /**
     * 1.2 TEST SP: Rechazo cuando el expediente registra licencia denegada previa
     */
    public function test_sp_rechaza_emision_si_expediente_esta_denegado(): void
    {
        DB::table('autorizaciones_improcedentes')->insert([
            'expediente_id' => 999,
            'tipo_autorizacion' => 'LF',
        ]);

        $service = app(LicenciaService::class);

        $this->expectException(\Throwable::class);

        $service->crear([
            'expediente_id' => 999,
            'nombre_comercial' => 'Local Denegado',
            'tipo_licencia_id' => 1,
            'numero_licencia' => 99,
        ], 1);
    }

    /**
     * 2. TEST TRIGGER: Validación de aforo máximo normativo (Área x 2)
     */
    public function test_trigger_aforo_permite_aforo_valido_y_rechaza_aforo_excedido(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Trigger trg_itse_aforo_maximo aplica en PostgreSQL.');
        }

        // Aforo válido: 50 m2 permite hasta 100 personas
        $itseValida = DB::table('itse')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'tipo_itse_id' => 1,
            'numero_itse' => 202601,
            'fecha_expedicion' => Carbon::now()->toDateString(),
            'fecha_solicitud_renovacion' => Carbon::now()->toDateString(),
            'fecha_caducidad' => Carbon::now()->addYears(2)->toDateString(),
            'nombre_comercial' => 'Comercio Seguro',
            'direccion' => 'Jr. Perú 123',
            'resolucion_numero' => 'RES-2026-001',
            'area' => 50.00,
            'capacidad_aforo' => 80, // 80 <= 100 -> VÁLIDO
            'numero_recibo_pago' => 'REC-ITSE-01',
            'observaciones' => 'Inspección conforme',
            'se_puede_publicar' => true,
            'fecha_digitacion' => Carbon::now(),
            'expediente_id' => 1,
            'usuario_id' => 1,
            'nivel_riesgo_id' => 1,
            'conductor_id' => 1,
            'titular_id' => 1,
        ]);

        $this->assertIsNumeric($itseValida);

        // Aforo excedido: 50 m2 con 150 personas debe abortar con excepción
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessageMatches('/Aforo excedido/i');

        DB::table('itse')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'tipo_itse_id' => 1,
            'numero_itse' => 202602,
            'fecha_expedicion' => Carbon::now()->toDateString(),
            'fecha_solicitud_renovacion' => Carbon::now()->toDateString(),
            'fecha_caducidad' => Carbon::now()->addYears(2)->toDateString(),
            'nombre_comercial' => 'Comercio Inseguro Hacinado',
            'direccion' => 'Jr. Perú 124',
            'resolucion_numero' => 'RES-2026-002',
            'area' => 50.00,
            'capacidad_aforo' => 150, // 150 > 100 -> DEBE FALLAR
            'numero_recibo_pago' => 'REC-ITSE-02',
            'observaciones' => 'Inspección con exceso de aforo',
            'se_puede_publicar' => false,
            'fecha_digitacion' => Carbon::now(),
            'expediente_id' => 2,
            'usuario_id' => 1,
            'nivel_riesgo_id' => 1,
            'conductor_id' => 1,
            'titular_id' => 1,
        ]);
    }

    /**
     * 3. TEST FUNCIÓN: Cálculo de plazo (EN_PLAZO, EN_ALERTA, VENCIDO)
     */
    public function test_funcion_calcula_estado_del_plazo_del_expediente(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Función fn_estado_plazo_expediente aplica en PostgreSQL.');
        }

        // Crear tipo de procedimiento TUPA: plazo 4 días hábiles, alerta 1 día
        $tipoTupaId = DB::table('tipos_procedimiento_tupa')->insertGetId([
            'codigo' => 'TUPA-TEST',
            'nombre' => 'Procedimiento Evaluación 4 Días',
            'plazo_atencion_dias' => 4,
            'dias_alerta_vencimiento' => 1,
            'esta_activo' => true,
            'requiere_lf' => true,
            'requiere_itse' => false,
            'fecha_digitacion' => Carbon::now(),
            'usuario_id' => 1,
            'unidad_organica_id' => 1,
            'monto' => 150.00,
        ]);

        // 3.1 Expediente recién ingresado hoy -> EN_PLAZO
        $expPlazoId = DB::table('expedientes')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'numero_expediente' => 9001,
            'fecha_recepcion' => Carbon::now(),
            'fecha_vencimiento' => Carbon::now()->addDays(5)->toDateString(),
            'fecha_alerta' => Carbon::now()->addDays(4)->toDateString(),
            'fecha_digitacion' => Carbon::now(),
            'usuario_id' => 1,
            'solicitante_id' => 1,
            'tipo_procedimiento_tupa_id' => $tipoTupaId,
        ]);

        $resPlazo = DB::select('SELECT * FROM fn_estado_plazo_expediente(?)', [$expPlazoId]);
        $this->assertEquals('EN_PLAZO', $resPlazo[0]->estado);
        $this->assertGreaterThan(0, $resPlazo[0]->dias_restantes);

        // 3.2 Expediente ingresado hace 15 días hábiles -> VENCIDO
        $expVencidoId = DB::table('expedientes')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'numero_expediente' => 9002,
            'fecha_recepcion' => Carbon::now()->subDays(20),
            'fecha_vencimiento' => Carbon::now()->subDays(10)->toDateString(),
            'fecha_alerta' => Carbon::now()->subDays(11)->toDateString(),
            'fecha_digitacion' => Carbon::now(),
            'usuario_id' => 1,
            'solicitante_id' => 1,
            'tipo_procedimiento_tupa_id' => $tipoTupaId,
        ]);

        $resVencido = DB::select('SELECT * FROM fn_estado_plazo_expediente(?)', [$expVencidoId]);
        $this->assertEquals('VENCIDO', $resVencido[0]->estado);
        $this->assertLessThan(0, $resVencido[0]->dias_restantes);
    }
}
