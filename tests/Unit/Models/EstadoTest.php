<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Estado;
use Tests\TestCase;

final class EstadoTest extends TestCase
{
    public function test_instancia_estado_y_logica_de_activo(): void
    {
        $estado = new Estado([
            'nombre' => 'Vigente',
            'es_para_lf' => true,
            'es_para_itse' => true,
            'esta_activo' => true,
        ]);

        $this->assertSame('Vigente', $estado->nombre);
        $this->assertTrue($estado->es_para_lf);
        $this->assertTrue($estado->es_para_itse);
        $this->assertTrue($estado->estaActivo());
    }

    public function test_casts_booleanos_funcionan_correctamente(): void
    {
        $estado = new Estado([
            'nombre' => 'Test',
            'es_para_lf' => 1,
            'es_para_itse' => 0,
            'esta_activo' => 0,
        ]);

        $this->assertTrue($estado->es_para_lf);
        $this->assertFalse($estado->es_para_itse);
        $this->assertFalse($estado->estaActivo());
    }
}
