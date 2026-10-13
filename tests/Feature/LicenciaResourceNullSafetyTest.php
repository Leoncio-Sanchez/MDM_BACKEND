<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Licencias\Presentation\Resources\LicenciaResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class LicenciaResourceNullSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

    }

    public function test_licencia_resource_maneja_recurso_nulo_sin_excepcion(): void
    {
        $resource = new LicenciaResource(null);
        $result = $resource->toArray(Request::create('/'));

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_licencia_resource_maneja_id_inexistente_sin_excepcion(): void
    {
        $resource = new LicenciaResource(['id' => 999999]);
        $result = $resource->toArray(Request::create('/'));

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_licencia_resource_mapea_correctamente_entidad_con_null_safety(): void
    {
        $id = \Illuminate\Support\Facades\DB::table('licencias_funcionamiento')->insertGetId(\Tests\Fixtures\Registros::licencia([
            'uuid' => '00000000-0000-4000-8000-000000001234',
            'numero_licencia' => 101,
            'nombre_comercial' => 'Bodega Test',
            'tipo_licencia_id' => 1,
            'titular_id' => 1,
            'conductor_id' => 1,
            'expediente_id' => 1,
            'nivel_riesgo_id' => 1,
            'zonificacion_id' => 1,
            'area' => 45.5,
            'es_vigencia_indeterminada' => true,
            'se_puede_publicar' => true,
            'usuario_id' => 1,
        ]));

        $resource = new LicenciaResource(['id' => $id]);
        $result = $resource->toArray(Request::create('/'));

        $this->assertIsArray($result);
        $this->assertSame($id, $result['id']);
        $this->assertSame('Bodega Test', $result['nombre_comercial']);
        $this->assertSame('45.50', $result['area']);
    }
}
