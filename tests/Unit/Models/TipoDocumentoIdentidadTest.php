<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\TipoDocumentoIdentidad;
use Tests\TestCase;

final class TipoDocumentoIdentidadTest extends TestCase
{
    public function test_instancia_tipo_documento_identidad_y_logica_de_activo(): void
    {
        $tipo = new TipoDocumentoIdentidad([
            'codigo' => '01',
            'nombre' => 'DNI',
            'esta_activo' => true,
            'es_para_natural' => true,
            'es_para_juridica' => false,
        ]);

        $this->assertSame('01', $tipo->codigo);
        $this->assertSame('DNI', $tipo->nombre);
        $this->assertTrue($tipo->esta_activo);
        $this->assertTrue($tipo->estaActivo());
        $this->assertTrue($tipo->es_para_natural);
        $this->assertFalse($tipo->es_para_juridica);
    }

    public function test_casts_funcionan_correctamente(): void
    {
        $tipo = new TipoDocumentoIdentidad([
            'codigo' => '06',
            'nombre' => 'RUC',
            'esta_activo' => 0,
            'es_para_natural' => 1,
            'es_para_juridica' => 1,
        ]);
        $tipo->id = 3;

        $this->assertSame(3, $tipo->id);
        $this->assertSame('06', $tipo->codigo);
        $this->assertSame('RUC', $tipo->nombre);
        $this->assertFalse($tipo->esta_activo);
        $this->assertFalse($tipo->estaActivo());
        $this->assertTrue($tipo->es_para_natural);
        $this->assertTrue($tipo->es_para_juridica);
    }
}
