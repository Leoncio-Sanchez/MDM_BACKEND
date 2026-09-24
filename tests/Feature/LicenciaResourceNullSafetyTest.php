<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Licencias\Presentation\Resources\LicenciaResource;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class LicenciaResourceNullSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('licencias_funcionamiento_giros')) {
            Schema::create('licencias_funcionamiento_giros', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('licencia_funcionamiento_id');
                $table->integer('giro_id');
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        if (! Schema::hasTable('licencias_funcionamiento_archivos')) {
            Schema::create('licencias_funcionamiento_archivos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid')->nullable();
                $table->integer('licencia_funcionamiento_id');
                $table->string('nombre_original')->nullable();
                $table->string('nombre_archivo')->nullable();
                $table->string('ruta_archivo')->nullable();
                $table->string('extension')->nullable();
                $table->bigInteger('tamanio_bytes')->default(0);
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        if (! Schema::hasTable('licencias_funcionamiento_estados')) {
            Schema::create('licencias_funcionamiento_estados', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('licencia_funcionamiento_id');
                $table->integer('estado_id');
                $table->date('fecha_estado')->nullable();
                $table->string('documento')->nullable();
                $table->text('observaciones')->nullable();
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        if (! Schema::hasTable('licencias_funcionamiento')) {
            Schema::create('licencias_funcionamiento', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid')->nullable();
                $table->integer('expediente_id')->nullable();
                $table->integer('tipo_licencia_id')->nullable();
                $table->integer('numero_licencia')->nullable();
                $table->date('fecha_emision')->nullable();
                $table->integer('titular_id')->nullable();
                $table->integer('conductor_id')->nullable();
                $table->integer('licencia_principal_id')->nullable();
                $table->string('nombre_comercial')->nullable();
                $table->boolean('es_vigencia_indeterminada')->default(true);
                $table->date('fecha_inicio_vigencia')->nullable();
                $table->date('fecha_fin_vigencia')->nullable();
                $table->integer('nivel_riesgo_id')->nullable();
                $table->text('actividad')->nullable();
                $table->string('direccion')->nullable();
                $table->integer('hora_desde')->default(0);
                $table->integer('hora_hasta')->default(0);
                $table->string('resolucion_numero')->nullable();
                $table->integer('zonificacion_id')->nullable();
                $table->decimal('area', 10, 2)->default(0);
                $table->string('numero_recibo_pago')->nullable();
                $table->text('observaciones')->nullable();
                $table->boolean('se_puede_publicar')->default(true);
                $table->timestamp('fecha_notificacion')->nullable();
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }
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
        $id = \Illuminate\Support\Facades\DB::table('licencias_funcionamiento')->insertGetId([
            'uuid' => 'test-uuid-1234',
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
        ]);

        $resource = new LicenciaResource(['id' => $id]);
        $result = $resource->toArray(Request::create('/'));

        $this->assertIsArray($result);
        $this->assertSame($id, $result['id']);
        $this->assertSame('Bodega Test', $result['nombre_comercial']);
        $this->assertSame('45.50', $result['area']);
    }
}
