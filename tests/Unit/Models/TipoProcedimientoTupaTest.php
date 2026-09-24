<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\TipoProcedimientoTupa;
use Tests\TestCase;

final class TipoProcedimientoTupaTest extends TestCase
{
    public function test_instancia_tipo_procedimiento_tupa_y_logica_de_activo(): void
    {
        $tipo = new TipoProcedimientoTupa([
            'codigo' => 'TUPA01',
            'nombre' => 'LICENCIA FUNCIONAMIENTO EDIFICACION',
            'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 3,
            'esta_activo' => true,
            'requiere_lf' => true,
            'requiere_itse' => false,
            'unidad_organica_id' => 1,
            'usuario_id' => 1,
        ]);

        $this->assertSame('TUPA01', $tipo->codigo);
        $this->assertSame('LICENCIA FUNCIONAMIENTO EDIFICACION', $tipo->nombre);
        $this->assertSame(10, $tipo->plazo_atencion_dias);
        $this->assertSame(3, $tipo->dias_alerta_vencimiento);
        $this->assertTrue($tipo->esta_activo);
        $this->assertTrue($tipo->estaActivo());
        $this->assertTrue($tipo->requiere_lf);
        $this->assertFalse($tipo->requiere_itse);
        $this->assertSame(1, $tipo->unidad_organica_id);
        $this->assertSame(1, $tipo->usuario_id);
    }

    public function test_casts_funcionan_correctamente(): void
    {
        $tipo = new TipoProcedimientoTupa([
            'codigo' => 'TUPA02',
            'nombre' => 'CESE DE ACTIVIDADES',
            'plazo_atencion_dias' => '15',
            'dias_alerta_vencimiento' => '5',
            'esta_activo' => 0,
            'requiere_lf' => 1,
            'requiere_itse' => 0,
            'unidad_organica_id' => '2',
            'usuario_id' => '3',
        ]);
        $tipo->id = 5;

        $this->assertSame(5, $tipo->id);
        $this->assertSame('TUPA02', $tipo->codigo);
        $this->assertSame('CESE DE ACTIVIDADES', $tipo->nombre);
        $this->assertSame(15, $tipo->plazo_atencion_dias);
        $this->assertSame(5, $tipo->dias_alerta_vencimiento);
        $this->assertFalse($tipo->esta_activo);
        $this->assertFalse($tipo->estaActivo());
        $this->assertTrue($tipo->requiere_lf);
        $this->assertFalse($tipo->requiere_itse);
        $this->assertSame(2, $tipo->unidad_organica_id);
        $this->assertSame(3, $tipo->usuario_id);
    }
}
