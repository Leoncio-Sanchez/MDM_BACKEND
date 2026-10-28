<?php

declare(strict_types=1);

namespace Tests\Feature\BaseDeDatos;

use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;
use DateTimeImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\TestCase;

/**
 * Funciones y trigger de PostgreSQL del Corte 2 (equipo), integrados en main.
 * Basado en tests/Feature/StoredProceduresTest.php de la rama corte2.
 */
final class FuncionesCorte2Test extends TestCase
{
    use RefreshDatabase;

    // ── Trigger de aforo (trg_itse_aforo_maximo) ──────────────────────

    public function test_el_trigger_permite_aforo_valido_y_rechaza_el_excedido(): void
    {
        $valido = DB::table('itse')->insertGetId(Registros::itse(['area' => 50, 'capacidad_aforo' => 100]));
        $this->assertIsNumeric($valido);

        try {
            DB::table('itse')->insert(Registros::itse(['area' => 50, 'capacidad_aforo' => 101]));
            $this->fail('El trigger debió rechazar el aforo excedido');
        } catch (QueryException $e) {
            $this->assertSame('LF001', $e->errorInfo[0]);
            $this->assertStringContainsString('Aforo excedido: el aforo máximo permitido es de 100 personas', $e->getMessage());
        }
    }

    public function test_un_certificado_antiguo_que_no_cumple_se_puede_seguir_actualizando_en_lo_demas(): void
    {
        // Registro anterior a la regla: se inserta sin disparar triggers (solo para este insert)
        DB::statement('SET session_replication_role = replica');
        $antiguo = DB::table('itse')->insertGetId(Registros::itse(['area' => 10, 'capacidad_aforo' => 80]));
        DB::statement('SET session_replication_role = DEFAULT');

        // Registrar la notificación no toca área ni aforo: no se bloquea
        DB::table('itse')->where('id', $antiguo)->update(['fecha_notificacion' => now()]);
        $this->assertNotNull(DB::table('itse')->where('id', $antiguo)->value('fecha_notificacion'));

        // Cambiar el aforo sí se valida
        $this->expectException(QueryException::class);
        DB::table('itse')->where('id', $antiguo)->update(['capacidad_aforo' => 90]);
    }

    public function test_si_el_trigger_rechaza_la_api_responde_422_con_mensaje_claro(): void
    {
        $e = new QueryException('pgsql', 'insert into itse ...', [], new \PDOException('Aforo excedido'));
        $e->errorInfo = ['LF001', 7, 'ERROR: Aforo excedido'];

        $render = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render(\Illuminate\Http\Request::create('/api/lf-itse/itse/', 'POST', server: ['HTTP_ACCEPT' => 'application/json']), $e);

        $this->assertSame(422, $render->getStatusCode());
        $this->assertStringContainsString('Aforo excedido', (string) $render->getContent());
    }

    // ── Plazos en días hábiles (fn_estado_plazo_expediente) ───────────

    public function test_calcula_el_estado_del_plazo_del_expediente(): void
    {
        $tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa([
            'plazo_atencion_dias' => 15, 'dias_alerta_vencimiento' => 2,
        ]));

        $enPlazo = DB::table('expedientes')->insertGetId(Registros::expediente(['tipo_procedimiento_tupa_id' => $tupa, 'fecha_recepcion' => now()]));
        $vencido = DB::table('expedientes')->insertGetId(Registros::expediente(['tipo_procedimiento_tupa_id' => $tupa, 'fecha_recepcion' => now()->subDays(60)]));

        $a = DB::selectOne('SELECT * FROM fn_estado_plazo_expediente(?)', [$enPlazo]);
        $this->assertSame('EN_PLAZO', $a->estado);
        $this->assertGreaterThan(0, $a->dias_restantes);

        $b = DB::selectOne('SELECT * FROM fn_estado_plazo_expediente(?)', [$vencido]);
        $this->assertSame('VENCIDO', $b->estado);
        $this->assertLessThan(0, $b->dias_restantes);

        $c = DB::selectOne('SELECT * FROM fn_estado_plazo_expediente(?)', [999999]);
        $this->assertSame('NO_EXISTE', $c->estado);
    }

    public function test_la_recepcion_se_cuenta_en_hora_de_peru(): void
    {
        $tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa([
            'plazo_atencion_dias' => 1, 'dias_alerta_vencimiento' => 0,
        ]));
        // Miércoles 14/10/2026 a las 20:00 en Perú = jueves 15/10 a la 01:00 en UTC
        $exp = DB::table('expedientes')->insertGetId(Registros::expediente([
            'tipo_procedimiento_tupa_id' => $tupa, 'fecha_recepcion' => '2026-10-15 01:00:00+00',
        ]));

        // Recibido el miércoles 14 en Perú: vence el jueves 15 (1 día hábil), no el viernes 16
        $esperado = DB::selectOne(
            "SELECT fn_diferencia_dias_habiles((NOW() AT TIME ZONE 'America/Lima')::DATE, DATE '2026-10-15') AS dias"
        );
        $real = DB::selectOne('SELECT * FROM fn_estado_plazo_expediente(?)', [$exp]);

        $this->assertSame((int) $esperado->dias, (int) $real->dias_restantes);
    }

    public function test_la_funcion_sql_y_la_calculadora_php_dan_las_mismas_fechas(): void
    {
        DB::table('feriados_recurrentes')->insert(['dia' => 8, 'mes' => 12]);
        DB::table('feriados_anuales')->insert(['feriado' => '2026-12-24', 'fecha_digitacion' => now(), 'user_id' => 1]);

        /** @var CalculadoraDiasHabilesService $php */
        $php = $this->app->make(CalculadoraDiasHabilesService::class);

        $inicio = new DateTimeImmutable('2026-11-20');
        for ($d = 0; $d < 30; $d++) {
            $fecha = $inicio->modify("+{$d} days");
            foreach ([0, 1, 3, 7, 15] as $plazo) {
                $sql = DB::selectOne('SELECT fn_avanzar_dias_habiles(?::DATE, ?) AS f', [$fecha->format('Y-m-d'), $plazo])->f;
                $this->assertSame(
                    $php->calcularFechaVencimiento($fecha, $plazo)->format('Y-m-d'),
                    (string) $sql,
                    "Distinto para {$fecha->format('Y-m-d')} + {$plazo} días hábiles",
                );
            }
        }
    }
}
