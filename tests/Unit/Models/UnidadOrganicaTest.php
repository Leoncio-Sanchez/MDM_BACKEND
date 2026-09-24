<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\UnidadOrganica;
use Tests\TestCase;

final class UnidadOrganicaTest extends TestCase
{
    public function test_instancia_unidad_organica_y_logica_de_activo(): void
    {
        $unidad = new UnidadOrganica([
            'codigo' => 'OGRD',
            'nombre' => 'Oficina de Gestión de Riesgo de Desastres',
            'sigla' => 'OGRD',
            'esta_activo' => true,
        ]);

        $this->assertSame('OGRD', $unidad->codigo);
        $this->assertSame('Oficina de Gestión de Riesgo de Desastres', $unidad->nombre);
        $this->assertSame('OGRD', $unidad->sigla);
        $this->assertTrue($unidad->esta_activo);
        $this->assertTrue($unidad->estaActivo());
    }

    public function test_casts_funcionan_correctamente(): void
    {
        $unidad = new UnidadOrganica([
            'codigo' => 'SAT',
            'nombre' => 'Sub Gerencia de Administración Tributaria',
            'sigla' => 'SAT',
            'esta_activo' => 0,
        ]);
        $unidad->id = 1;

        $this->assertSame(1, $unidad->id);
        $this->assertSame('SAT', $unidad->codigo);
        $this->assertSame('Sub Gerencia de Administración Tributaria', $unidad->nombre);
        $this->assertSame('SAT', $unidad->sigla);
        $this->assertFalse($unidad->esta_activo);
        $this->assertFalse($unidad->estaActivo());
    }
}
