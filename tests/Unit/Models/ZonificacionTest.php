<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Zonificacion;
use Tests\TestCase;

final class ZonificacionTest extends TestCase
{
    public function test_instancia_zonificacion_y_logica_de_activo(): void
    {
        $zonificacion = new Zonificacion([
            'codigo' => 'ZDA',
            'nombre' => 'ZONA URBANA DE DENSIDAD ALTA',
            'esta_activo' => true,
            'usuario_id' => 1,
        ]);

        $this->assertSame('ZDA', $zonificacion->codigo);
        $this->assertSame('ZONA URBANA DE DENSIDAD ALTA', $zonificacion->nombre);
        $this->assertTrue($zonificacion->esta_activo);
        $this->assertTrue($zonificacion->estaActivo());
    }

    public function test_casts_funcionan_correctamente(): void
    {
        $zonificacion = new Zonificacion([
            'codigo' => 'ZDM',
            'nombre' => 'ZONA URBANA DE DENSIDAD MEDIA',
            'esta_activo' => 0,
            'usuario_id' => '2',
        ]);
        $zonificacion->id = 2;

        $this->assertSame(2, $zonificacion->id);
        $this->assertSame('ZDM', $zonificacion->codigo);
        $this->assertSame('ZONA URBANA DE DENSIDAD MEDIA', $zonificacion->nombre);
        $this->assertFalse($zonificacion->esta_activo);
        $this->assertFalse($zonificacion->estaActivo());
        $this->assertSame(2, $zonificacion->usuario_id);
    }
}
