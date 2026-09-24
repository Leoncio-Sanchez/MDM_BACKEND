<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Giro;
use Tests\TestCase;

final class GiroTest extends TestCase
{
    public function test_instancia_giro_y_logica_de_activo(): void
    {
        $giro = new Giro([
            'ciiu_id' => 1234,
            'nombre' => 'Comercio minorista',
            'esta_activo' => true,
            'usuario_id' => 1,
        ]);

        $this->assertSame(1234, $giro->ciiu_id);
        $this->assertSame('Comercio minorista', $giro->nombre);
        $this->assertTrue($giro->esta_activo);
        $this->assertTrue($giro->estaActivo());
    }

    public function test_casts_funcionan_correctamente(): void
    {
        $giro = new Giro([
            'ciiu_id' => '1234',
            'nombre' => 'Test',
            'esta_activo' => 0,
            'usuario_id' => '2',
        ]);

        $this->assertSame(1234, $giro->ciiu_id);
        $this->assertFalse($giro->esta_activo);
        $this->assertSame(2, $giro->usuario_id);
        $this->assertFalse($giro->estaActivo());
    }
}
