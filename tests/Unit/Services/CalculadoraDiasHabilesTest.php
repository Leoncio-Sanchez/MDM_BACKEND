<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\CalculadoraDiasHabiles;
use DateTimeImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CalculadoraDiasHabilesTest extends TestCase
{
    private CalculadoraDiasHabiles $calculadora;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('feriados_recurrentes')) {
            Schema::create('feriados_recurrentes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('dia');
                $table->integer('mes');
                $table->string('descripcion')->nullable();
            });
        }

        if (! Schema::hasTable('feriados_anuales')) {
            Schema::create('feriados_anuales', function (Blueprint $table) {
                $table->increments('id');
                $table->date('feriado');
                $table->string('descripcion')->nullable();
            });
        }

        DB::table('feriados_recurrentes')->truncate();
        DB::table('feriados_anuales')->truncate();

        // 1 de enero y 28 de julio como recurrentes
        DB::table('feriados_recurrentes')->insert([
            ['dia' => 1, 'mes' => 1, 'descripcion' => 'Año Nuevo'],
            ['dia' => 28, 'mes' => 7, 'descripcion' => 'Fiestas Patrias'],
        ]);

        // Feriado anual específico: 2026-05-15
        DB::table('feriados_anuales')->insert([
            ['feriado' => '2026-05-15', 'descripcion' => 'Feriado Puente'],
        ]);

        $this->calculadora = new CalculadoraDiasHabiles;
    }

    public function test_sabado_y_domingo_no_son_dias_habiles(): void
    {
        // 2026-05-02 es Sábado
        $sabado = new DateTimeImmutable('2026-05-02');
        // 2026-05-03 es Domingo
        $domingo = new DateTimeImmutable('2026-05-03');

        $this->assertFalse($this->calculadora->esDiaHabil($sabado));
        $this->assertFalse($this->calculadora->esDiaHabil($domingo));
    }

    public function test_dia_laborable_es_habil(): void
    {
        // 2026-05-04 es Lunes
        $lunes = new DateTimeImmutable('2026-05-04');
        $this->assertTrue($this->calculadora->esDiaHabil($lunes));
    }

    public function test_feriado_recurrente_no_es_habil(): void
    {
        // 2026-01-01 es Jueves (Año Nuevo)
        $feriado = new DateTimeImmutable('2026-01-01');
        $this->assertFalse($this->calculadora->esDiaHabil($feriado));
    }

    public function test_feriado_anual_no_es_habil(): void
    {
        // 2026-05-15 es Viernes (Feriado Puente)
        $feriado = new DateTimeImmutable('2026-05-15');
        $this->assertFalse($this->calculadora->esDiaHabil($feriado));
    }

    public function test_calcular_fecha_vencimiento_avanza_saltando_fines_de_semana_y_feriados(): void
    {
        // Desde 2026-05-14 (Jueves), plazo 2 días:
        // Viernes 2026-05-15 es feriado anual (salta)
        // Sábado 16 y Domingo 17 (salta)
        // Lunes 18 es día 1
        // Martes 19 es día 2 -> vencimiento 2026-05-19
        $inicio = new DateTimeImmutable('2026-05-14');
        $vencimiento = $this->calculadora->calcularFechaVencimiento($inicio, 2);

        $this->assertSame('2026-05-19', $vencimiento->format('Y-m-d'));
    }

    public function test_calcular_fecha_alerta_retrocede_dias_habiles(): void
    {
        // Desde 2026-05-19 (Martes), alerta 2 días antes:
        // Lunes 18 es día 1
        // Sábado y Domingo saltan
        // Viernes 15 es feriado (salta)
        // Jueves 14 es día 2 -> alerta 2026-05-14
        $vencimiento = new DateTimeImmutable('2026-05-19');
        $alerta = $this->calculadora->calcularFechaAlerta($vencimiento, 2);

        $this->assertSame('2026-05-14', $alerta->format('Y-m-d'));
    }

    public function test_diferencia_dias_habiles(): void
    {
        $inicio = new DateTimeImmutable('2026-05-14');
        $fin = new DateTimeImmutable('2026-05-19');

        $diferencia = $this->calculadora->diferenciaDiasHabiles($inicio, $fin);
        $this->assertSame(2, $diferencia);

        $negativa = $this->calculadora->diferenciaDiasHabiles($fin, $inicio);
        $this->assertSame(-2, $negativa);

        $cero = $this->calculadora->diferenciaDiasHabiles($inicio, $inicio);
        $this->assertSame(0, $cero);
    }

    public function test_plazo_cero_retorna_misma_fecha(): void
    {
        $fecha = new DateTimeImmutable('2026-05-04');
        $this->assertSame('2026-05-04', $this->calculadora->calcularFechaVencimiento($fecha, 0)->format('Y-m-d'));
        $this->assertSame('2026-05-04', $this->calculadora->calcularFechaAlerta($fecha, 0)->format('Y-m-d'));
    }
}
