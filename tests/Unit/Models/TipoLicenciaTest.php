<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\TipoLicencia;
use Tests\TestCase;

final class TipoLicenciaTest extends TestCase
{
    public function test_instancia_tipo_licencia_y_logica_de_activo(): void
    {
        $tipo = new TipoLicencia([
            'codigo' => 'EST',
            'nombre' => 'ESTANDAR',
            'esta_activo' => true,
        ]);

        $this->assertSame('EST', $tipo->codigo);
        $this->assertSame('ESTANDAR', $tipo->nombre);
        $this->assertTrue($tipo->esta_activo);
        $this->assertTrue($tipo->estaActivo());
    }

    public function test_casts_funcionan_correctamente(): void
    {
        $tipo = new TipoLicencia([
            'codigo' => 'CES',
            'nombre' => 'CESIONARIO',
            'esta_activo' => 0,
        ]);
        $tipo->id = 1;

        $this->assertSame(1, $tipo->id);
        $this->assertSame('CES', $tipo->codigo);
        $this->assertSame('CESIONARIO', $tipo->nombre);
        $this->assertFalse($tipo->esta_activo);
        $this->assertFalse($tipo->estaActivo());
    }
}
