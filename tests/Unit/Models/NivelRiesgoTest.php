<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\NivelRiesgo;
use Tests\TestCase;

final class NivelRiesgoTest extends TestCase
{
    public function test_instancia_nivel_riesgo_y_logica_de_activo(): void
    {
        $nivel = new NivelRiesgo([
            'codigo' => 'NRB',
            'nombre' => 'RIESGO BAJO',
            'esta_activo' => true,
        ]);

        $this->assertSame('NRB', $nivel->codigo);
        $this->assertSame('RIESGO BAJO', $nivel->nombre);
        $this->assertTrue($nivel->esta_activo);
        $this->assertTrue($nivel->estaActivo());
    }

    public function test_casts_funcionan_correctamente(): void
    {
        $nivel = new NivelRiesgo([
            'codigo' => 'NRM',
            'nombre' => 'RIESGO MEDIO',
            'esta_activo' => 0,
        ]);
        $nivel->id = 2;

        $this->assertSame(2, $nivel->id);
        $this->assertSame('NRM', $nivel->codigo);
        $this->assertSame('RIESGO MEDIO', $nivel->nombre);
        $this->assertFalse($nivel->esta_activo);
        $this->assertFalse($nivel->estaActivo());
    }
}
