<?php

declare(strict_types=1);

namespace Tests\Feature\Expedientes;

use App\Models\Expediente;
use App\Models\Usuario as UsuarioModel;
use App\Services\DjangoJwtTokenService;
use App\Services\AuthenticatedUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ExpedienteControllerTest extends TestCase
{
    private string $token;

    private int $expedienteId;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('auth_user')) {
            Schema::create('auth_user', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('password');
                $table->string('first_name')->default('');
                $table->string('last_name')->default('');
                $table->string('email')->default('');
                $table->boolean('is_superuser')->default(false);
                $table->boolean('is_staff')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login')->nullable();
                $table->timestamp('date_joined')->nullable();
            });
        }

        if (! Schema::hasTable('unidades_organicas')) {
            Schema::create('unidades_organicas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo', 20)->unique();
                $table->string('nombre', 100);
                $table->boolean('esta_activo')->default(true);
            });
        }

        if (! Schema::hasTable('tipos_procedimiento_tupa')) {
            Schema::create('tipos_procedimiento_tupa', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo', 30)->unique();
                $table->string('nombre', 200);
                $table->integer('plazo_atencion_dias');
                $table->integer('dias_alerta_vencimiento');
                $table->boolean('esta_activo')->default(true);
                $table->boolean('requiere_lf')->default(false);
                $table->boolean('requiere_itse')->default(false);
                $table->dateTime('fecha_digitacion')->nullable();
                $table->integer('usuario_id')->nullable();
                $table->integer('unidad_organica_id')->nullable();
                $table->decimal('monto', 18, 2)->nullable();
            });
        }

        if (! Schema::hasTable('personas')) {
            Schema::create('personas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('tipo_persona', 1)->default('N');
                $table->string('sexo', 1)->default('X');
                $table->string('apellido_paterno', 50)->nullable();
                $table->string('apellido_materno', 50)->nullable();
                $table->string('nombres', 100);
                $table->string('direccion', 250)->nullable();
                $table->string('distrito', 100)->nullable();
                $table->string('provincia', 100)->nullable();
                $table->string('departamento', 100)->nullable();
                $table->string('telefono', 30)->nullable();
                $table->string('correo_electronico', 150)->nullable();
                $table->integer('user_id')->default(1);
            });
        }

        if (! Schema::hasTable('tipos_documento_identidad')) {
            Schema::create('tipos_documento_identidad', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo', 20)->unique();
                $table->string('nombre', 100);
                $table->boolean('esta_activo')->default(true);
                $table->boolean('es_para_natural')->default(true);
                $table->boolean('es_para_juridica')->default(true);
            });
        }

        if (! Schema::hasTable('personas_documentos')) {
            Schema::create('personas_documentos', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('persona_id');
                $table->integer('tipo_documento_identidad_id');
                $table->string('numero_documento', 20);
            });
        }

        if (! Schema::hasTable('expedientes')) {
            Schema::create('expedientes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid', 36)->nullable();
                $table->integer('numero_expediente')->nullable();
                $table->dateTime('fecha_recepcion')->nullable();
                $table->date('fecha_ingreso')->nullable();
                $table->integer('tipo_procedimiento_tupa_id')->nullable();
                $table->integer('solicitante_id')->nullable();
                $table->integer('representante_id')->nullable();
                $table->string('observaciones', 250)->nullable();
                $table->date('fecha_vencimiento')->nullable();
                $table->date('fecha_alerta')->nullable();
                $table->date('fecha_suspension')->nullable();
                $table->integer('dias_ampliacion')->nullable();
                $table->string('motivo_ampliacion', 250)->nullable();
                $table->integer('usuario_ampliacion')->nullable();
                $table->dateTime('fecha_digitacion_ampliacion')->nullable();
                $table->integer('usuario_id')->nullable();
                $table->dateTime('fecha_digitacion')->nullable();
            });
        }

        if (! Schema::hasTable('autorizaciones_improcedentes')) {
            Schema::create('autorizaciones_improcedentes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('expediente_id');
                $table->string('tipo_autorizacion', 10);
                $table->dateTime('fecha_rechazo')->nullable();
                $table->string('documento', 100)->nullable();
                $table->string('observaciones', 250)->nullable();
                $table->integer('usuario_id')->nullable();
                $table->dateTime('fecha_digitacion')->nullable();
                $table->boolean('esta_activo')->default(true);
            });
        }

        if (! Schema::hasTable('feriados_recurrentes')) {
            Schema::create('feriados_recurrentes', function (Blueprint $table) {
                $table->increments('id');
                $table->smallInteger('dia');
                $table->smallInteger('mes');
                $table->unique(['dia', 'mes']);
            });
        }

        if (! Schema::hasTable('feriados_anuales')) {
            Schema::create('feriados_anuales', function (Blueprint $table) {
                $table->increments('id');
                $table->date('feriado')->unique();
                $table->dateTime('fecha_digitacion')->nullable();
                $table->integer('user_id')->nullable();
            });
        }

        if (! Schema::hasTable('licencias_funcionamiento')) {
            Schema::create('licencias_funcionamiento', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid', 36)->nullable();
                $table->integer('numero_licencia')->nullable();
                $table->date('fecha_emision')->nullable();
                $table->string('nombre_comercial', 250)->default('');
                $table->integer('titular_id')->nullable();
                $table->integer('conductor_id')->nullable();
                $table->integer('expediente_id')->nullable();
                $table->integer('tipo_licencia_id')->nullable();
                $table->integer('nivel_riesgo_id')->nullable();
                $table->integer('zonificacion_id')->nullable();
                $table->boolean('es_vigencia_indeterminada')->default(true);
                $table->string('actividad', 50)->default('');
                $table->string('direccion', 250)->default('');
                $table->integer('hora_desde')->default(8);
                $table->integer('hora_hasta')->default(20);
                $table->string('resolucion_numero', 50)->default('');
                $table->decimal('area', 18, 2)->default(50);
                $table->string('numero_recibo_pago', 20)->default('');
                $table->string('estado', 20)->default('EMITIDA');
            });
        }

        if (! Schema::hasTable('itse')) {
            Schema::create('itse', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid', 36)->nullable();
                $table->integer('numero_itse')->nullable();
                $table->date('fecha_expedicion')->nullable();
                $table->date('fecha_caducidad')->nullable();
                $table->string('nombre_comercial', 200)->default('');
                $table->string('direccion', 300)->default('');
                $table->string('resolucion_numero', 100)->default('');
                $table->decimal('area', 10, 2)->default(0);
                $table->integer('capacidad_aforo')->default(0);
                $table->string('numero_recibo_pago', 50)->nullable();
                $table->integer('tipo_itse_id')->nullable();
                $table->integer('expediente_id')->nullable();
                $table->integer('usuario_id')->nullable();
                $table->integer('nivel_riesgo_id')->nullable();
                $table->integer('conductor_id')->nullable();
                $table->integer('titular_id')->nullable();
            });
        }

        // Limpiar datos
        DB::table('itse')->delete();
        DB::table('licencias_funcionamiento')->delete();
        DB::table('autorizaciones_improcedentes')->delete();
        DB::table('expedientes')->delete();
        DB::table('feriados_anuales')->delete();
        DB::table('feriados_recurrentes')->delete();
        DB::table('personas_documentos')->delete();
        DB::table('tipos_documento_identidad')->delete();
        DB::table('personas')->delete();
        DB::table('tipos_procedimiento_tupa')->delete();
        DB::table('unidades_organicas')->delete();
        UsuarioModel::query()->delete();

        // Crear usuario admin y token JWT
        UsuarioModel::create([
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@muni.gob.pe',
            'password' => 'somehash',
            'is_superuser' => true,
            'is_staff' => true,
            'is_active' => true,
        ]);

        /** @var DjangoJwtTokenService $tokenService */
        $tokenService = app(DjangoJwtTokenService::class);
        $authUser = new AuthenticatedUser(
            id: 1,
            username: 'admin',
            email: 'admin@muni.gob.pe',
            role: 'ADMIN_MUNICIPALIDAD',
            ruc: null,
            passwordHash: 'somehash'
        );
        $this->token = $tokenService->issue($authUser)->access;

        // Semillas
        DB::table('unidades_organicas')->insert([
            'id' => 1,
            'codigo' => 'UO-01',
            'nombre' => 'Unidad Organica 1',
            'esta_activo' => true,
        ]);

        DB::table('tipos_procedimiento_tupa')->insert([
            'id' => 1,
            'codigo' => 'TUPA-01',
            'nombre' => 'Procedimiento TUPA 1',
            'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 3,
            'esta_activo' => true,
            'requiere_lf' => true,
            'requiere_itse' => true,
            'fecha_digitacion' => '2026-01-01 00:00:00',
            'usuario_id' => 1,
            'unidad_organica_id' => 1,
            'monto' => 50.00,
        ]);

        DB::table('tipos_documento_identidad')->insert([
            ['id' => 1, 'codigo' => '01', 'nombre' => 'DNI', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false],
            ['id' => 2, 'codigo' => '06', 'nombre' => 'RUC', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true],
        ]);

        DB::table('personas')->insert([
            [
                'id' => 1,
                'tipo_persona' => 'N',
                'sexo' => 'M',
                'nombres' => 'JUAN',
                'apellido_paterno' => 'PEREZ',
                'apellido_materno' => 'GOMEZ',
                'direccion' => 'CALLE 1 #123',
                'distrito' => 'LIMA',
                'provincia' => 'LIMA',
                'departamento' => 'LIMA',
                'telefono' => '999888777',
                'correo_electronico' => 'juan@test.com',
                'user_id' => 1,
            ],
            [
                'id' => 2,
                'tipo_persona' => 'N',
                'sexo' => 'F',
                'nombres' => 'MARIA',
                'apellido_paterno' => 'LOPEZ',
                'apellido_materno' => 'DIAZ',
                'direccion' => 'CALLE 2 #456',
                'distrito' => 'LIMA',
                'provincia' => 'LIMA',
                'departamento' => 'LIMA',
                'telefono' => '999111222',
                'correo_electronico' => 'maria@test.com',
                'user_id' => 1,
            ],
        ]);

        DB::table('personas_documentos')->insert([
            ['id' => 1, 'persona_id' => 1, 'tipo_documento_identidad_id' => 2, 'numero_documento' => '20123456789'],
            ['id' => 2, 'persona_id' => 2, 'tipo_documento_identidad_id' => 1, 'numero_documento' => '12345678'],
        ]);

        // Crear un expediente inicial
        $this->expedienteId = (int) DB::table('expedientes')->insertGetId([
            'uuid' => 'c19fe3b5-05a6-401f-9e34-86c03effb0d4',
            'numero_expediente' => 100,
            'fecha_recepcion' => '2026-03-10 10:00:00',
            'fecha_ingreso' => '2026-03-10',
            'tipo_procedimiento_tupa_id' => 1,
            'solicitante_id' => 1,
            'representante_id' => 2,
            'observaciones' => 'Expediente inicial',
            'fecha_vencimiento' => '2026-03-24',
            'fecha_alerta' => '2026-03-19',
            'usuario_id' => 1,
            'fecha_digitacion' => '2026-03-10 10:00:00',
        ]);
    }

    public function test_unauthenticated_requests_return_401(): void
    {
        $this->getJson('/api/lf-itse/expedientes/')->assertStatus(401);
        $this->postJson('/api/lf-itse/expedientes/', [])->assertStatus(401);
        $this->getJson('/api/lf-itse/expedientes/1/')->assertStatus(401);
        $this->putJson('/api/lf-itse/expedientes/1/', [])->assertStatus(401);
        $this->deleteJson('/api/lf-itse/expedientes/1/')->assertStatus(401);
        $this->getJson('/api/lf-itse/expedientes/pendientes/')->assertStatus(401);
        $this->getJson('/api/lf-itse/expedientes/buscar/?filtro=NUMERO&valor=100')->assertStatus(401);
        $this->getJson('/api/lf-itse/expedientes/consulta/')->assertStatus(401);
        $this->postJson('/api/lf-itse/expedientes/1/ampliacion-plazo/', [])->assertStatus(401);
        $this->postJson('/api/lf-itse/expedientes/1/denegar-licencia/', [])->assertStatus(401);
        $this->postJson('/api/lf-itse/expedientes/1/denegar-itse/', [])->assertStatus(401);
        $this->getJson('/api/lf-itse/expedientes/1/autorizacion-improcedente/?tipo=LF')->assertStatus(401);
    }

    public function test_index_retorna_lista_con_8_keys(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/expedientes/');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $expectedKeys = [
            'id', 'uuid', 'numero_expediente', 'fecha_recepcion',
            'fecha_vencimiento', 'fecha_alerta', 'tipo_procedimiento_tupa', 'solicitante',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data[0]);
        }
        $this->assertCount(8, array_keys($data[0]));
    }

    public function test_store_crea_expediente_con_19_keys_y_calcula_fechas(): void
    {
        $payload = [
            'fecha_recepcion' => '2026-04-01T10:00:00',
            'tipo_procedimiento_tupa_id' => 1,
            'solicitante_id' => 1,
            'representante_id' => 2,
            'observaciones' => 'Nuevo expediente',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/expedientes/', $payload);

        $response->assertStatus(201);
        $data = $response->json();

        $expectedKeys = [
            'id', 'archivos', 'autorizaciones_improcedentes', 'uuid', 'numero_expediente',
            'fecha_recepcion', 'observaciones', 'fecha_vencimiento', 'fecha_alerta',
            'fecha_suspension', 'dias_ampliacion', 'motivo_ampliacion',
            'fecha_digitacion_ampliacion', 'fecha_digitacion',
            'tipo_procedimiento_tupa', 'solicitante', 'representante',
            'usuario_ampliacion', 'usuario',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data, "Falta key: {$key}");
        }
        $this->assertCount(19, array_keys($data));
        $this->assertEquals(1, $data['solicitante']);
        $this->assertEquals(2, $data['representante']);
        $this->assertEquals(1, $data['tipo_procedimiento_tupa']);
        $this->assertNotNull($data['fecha_vencimiento']);
        $this->assertNotNull($data['fecha_alerta']);
    }

    public function test_store_con_numero_expediente_explicito(): void
    {
        $payload = [
            'numero_expediente' => 999,
            'fecha_recepcion' => '2026-04-01T10:00:00',
            'tipo_procedimiento_tupa_id' => 1,
            'solicitante_id' => 1,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/expedientes/', $payload);

        $response->assertStatus(201);
        $this->assertEquals(999, $response->json('numero_expediente'));
    }

    public function test_store_falla_validacion_retorna_400(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/expedientes/', [
                'tipo_procedimiento_tupa_id' => 9999,
            ]);

        $response->assertStatus(400);
    }

    public function test_store_duplicado_retorna_409(): void
    {
        $payload = [
            'numero_expediente' => 888,
            'fecha_recepcion' => '2026-04-01T10:00:00',
            'tipo_procedimiento_tupa_id' => 1,
            'solicitante_id' => 1,
        ];

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/expedientes/', $payload)
            ->assertStatus(201);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/expedientes/', $payload);

        $response->assertStatus(409);
    }

    public function test_show_retorna_expediente_con_19_keys(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/lf-itse/expedientes/{$this->expedienteId}/");

        $response->assertStatus(200);
        $data = $response->json();

        $expectedKeys = [
            'id', 'archivos', 'autorizaciones_improcedentes', 'uuid', 'numero_expediente',
            'fecha_recepcion', 'observaciones', 'fecha_vencimiento', 'fecha_alerta',
            'fecha_suspension', 'dias_ampliacion', 'motivo_ampliacion',
            'fecha_digitacion_ampliacion', 'fecha_digitacion',
            'tipo_procedimiento_tupa', 'solicitante', 'representante',
            'usuario_ampliacion', 'usuario',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data, "Falta key: {$key}");
        }
        $this->assertCount(19, array_keys($data));
        $this->assertEquals($this->expedienteId, $data['id']);
    }

    public function test_show_retorna_404_si_no_existe(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/expedientes/99999/')
            ->assertStatus(404);
    }

    public function test_update_actualiza_expediente_y_retorna_19_keys(): void
    {
        $updatePayload = [
            'numero_expediente' => 555,
            'fecha_recepcion' => '2026-03-12T09:00:00',
            'tipo_procedimiento_tupa_id' => 1,
            'solicitante_id' => 1,
            'representante_id' => 2,
            'observaciones' => 'Observacion actualizada',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/lf-itse/expedientes/{$this->expedienteId}/", $updatePayload);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(19, array_keys($data));
        $this->assertEquals(555, $data['numero_expediente']);
        $this->assertEquals('Observacion actualizada', $data['observaciones']);
    }

    public function test_update_retorna_404_si_no_existe(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/expedientes/99999/', [
                'fecha_recepcion' => '2026-03-12T09:00:00',
                'tipo_procedimiento_tupa_id' => 1,
                'solicitante_id' => 1,
            ])
            ->assertStatus(404);
    }

    public function test_destroy_elimina_expediente_correctamente(): void
    {
        $toDeleteId = (int) DB::table('expedientes')->insertGetId([
            'numero_expediente' => 777,
            'fecha_recepcion' => '2026-03-10 10:00:00',
            'tipo_procedimiento_tupa_id' => 1,
            'solicitante_id' => 1,
            'usuario_id' => 1,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/lf-itse/expedientes/{$toDeleteId}/")
            ->assertStatus(204);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/lf-itse/expedientes/{$toDeleteId}/")
            ->assertStatus(404);
    }

    public function test_destroy_retorna_404_si_no_existe(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/expedientes/99999/')
            ->assertStatus(404);
    }

    public function test_destroy_retorna_409_si_tiene_licencia_asociada(): void
    {
        DB::table('licencias_funcionamiento')->insert([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'numero_licencia' => 101,
            'fecha_emision' => '2026-03-15',
            'nombre_comercial' => 'Mi Negocio',
            'titular_id' => 1,
            'conductor_id' => 1,
            'expediente_id' => $this->expedienteId,
            'tipo_licencia_id' => 1,
            'nivel_riesgo_id' => 1,
            'zonificacion_id' => 1,
            'es_vigencia_indeterminada' => true,
            'actividad' => 'Ventas',
            'direccion' => 'Av Principal 123',
            'hora_desde' => 8,
            'hora_hasta' => 20,
            'resolucion_numero' => 'RES-101',
            'area' => 50.00,
            'numero_recibo_pago' => 'REC-101',
            'estado' => 'EMITIDA',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/lf-itse/expedientes/{$this->expedienteId}/")
            ->assertStatus(409);
    }

    public function test_destroy_retorna_409_si_tiene_itse_asociado(): void
    {
        // Limpiar la licencia previa si hubiere
        DB::table('licencias_funcionamiento')->delete();

        DB::table('itse')->insert([
            'uuid' => '22222222-2222-2222-2222-222222222222',
            'numero_itse' => 201,
            'fecha_expedicion' => '2026-03-15',
            'fecha_caducidad' => '2028-03-15',
            'nombre_comercial' => 'ITSE Local',
            'direccion' => 'Av Principal 123',
            'resolucion_numero' => 'RES-ITSE-01',
            'area' => 100.00,
            'capacidad_aforo' => 20,
            'numero_recibo_pago' => 'REC-ITSE-01',
            'tipo_itse_id' => 1,
            'expediente_id' => $this->expedienteId,
            'usuario_id' => 1,
            'nivel_riesgo_id' => 1,
            'conductor_id' => 1,
            'titular_id' => 1,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/lf-itse/expedientes/{$this->expedienteId}/")
            ->assertStatus(409);
    }

    public function test_pendientes_retorna_200_con_11_keys(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/expedientes/pendientes/');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $expectedKeys = [
            'id', 'numero_expediente', 'fecha_recepcion', 'nombre',
            'fecha_vencimiento', 'fecha_alerta', 'persona_nombre',
            'licencia_pendiente', 'itse_pendiente', 'mostrar_alerta',
            'dias_habiles_restantes',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data[0], "Falta key en pendientes: {$key}");
        }
        $this->assertCount(11, array_keys($data[0]));
    }

    public function test_buscar_sin_filtro_o_valor_retorna_400(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/expedientes/buscar/?valor=100')
            ->assertStatus(400)
            ->assertJson(['error' => "El parámetro 'filtro' es obligatorio."]);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/expedientes/buscar/?filtro=NUMERO')
            ->assertStatus(400)
            ->assertJson(['error' => "El parámetro 'valor' es obligatorio."]);
    }

    public function test_buscar_con_filtro_retorna_200_con_27_keys(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/expedientes/buscar/?filtro=NUMERO&valor=100');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $expectedKeys = [
            'id', 'tipo_procedimiento_tupa_id', 'numero_expediente', 'fecha_recepcion',
            'solicitante_id', 'representante_id', 'observaciones', 'fecha_vencimiento',
            'fecha_alerta', 'fecha_suspension', 'dias_ampliacion', 'motivo_ampliacion',
            'usuario_ampliacion', 'fecha_digitacion_ampliacion', 'usuario_id',
            'fecha_digitacion', 'nombre_procedimiento', 'plazo_atencion_dias',
            'dias_alerta_vencimiento', 'requiere_itse', 'requiere_lf',
            'solicitante_nombre', 'solicitante_ruc', 'licencia_pendiente',
            'itse_pendiente', 'mostrar_alerta', 'dias_habiles_restantes',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data[0], "Falta key en buscar: {$key}");
        }
        $this->assertCount(27, array_keys($data[0]));
        $this->assertEquals(100, $data[0]['numero_expediente']);
    }

    public function test_consulta_retorna_200_con_19_keys(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/expedientes/consulta/');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $this->assertCount(19, array_keys($data[0]));
    }

    public function test_ampliacion_plazo_retorna_200_con_19_keys_y_recalcula_fechas(): void
    {
        $payload = [
            'fecha_suspension' => '2026-03-15',
            'dias_ampliacion' => 5,
            'motivo_ampliacion' => 'Subsanación de documentos',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/lf-itse/expedientes/{$this->expedienteId}/ampliacion-plazo/", $payload);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(19, array_keys($data));
        $this->assertEquals(5, $data['dias_ampliacion']);
        $this->assertEquals('Subsanación de documentos', $data['motivo_ampliacion']);
        $this->assertEquals('2026-03-15', $data['fecha_suspension']);
    }

    public function test_denegar_licencia_retorna_201_con_8_keys_y_400_si_repetido(): void
    {
        $payload = [
            'fecha_rechazo' => '2026-03-20',
            'documento' => 'RES-DENEGAR-LF-001',
            'observaciones' => 'Requisitos no cumplidos',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/lf-itse/expedientes/{$this->expedienteId}/denegar-licencia/", $payload);

        $response->assertStatus(201);
        $data = $response->json();

        $expectedKeys = [
            'id', 'expediente', 'tipo_autorizacion', 'fecha_rechazo',
            'documento', 'observaciones', 'usuario', 'fecha_digitacion',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data, "Falta key en denegar-licencia: {$key}");
        }
        $this->assertCount(8, array_keys($data));
        $this->assertEquals('LF', $data['tipo_autorizacion']);
        $this->assertEquals('RES-DENEGAR-LF-001', $data['documento']);

        // Insertar licencia para que falle con 400
        DB::table('licencias_funcionamiento')->insert([
            'uuid' => '33333333-3333-3333-3333-333333333333',
            'numero_licencia' => 888,
            'fecha_emision' => '2026-03-21',
            'nombre_comercial' => 'Local Denegado',
            'expediente_id' => $this->expedienteId,
            'estado' => 'EMITIDA',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/lf-itse/expedientes/{$this->expedienteId}/denegar-licencia/", $payload)
            ->assertStatus(400);
    }

    public function test_denegar_itse_retorna_201_con_8_keys_y_400_si_repetido(): void
    {
        $payload = [
            'fecha_rechazo' => '2026-03-20',
            'documento' => 'RES-DENEGAR-ITSE-001',
            'observaciones' => 'Inspección reprobada',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/lf-itse/expedientes/{$this->expedienteId}/denegar-itse/", $payload);

        $response->assertStatus(201);
        $data = $response->json();

        $expectedKeys = [
            'id', 'expediente', 'tipo_autorizacion', 'fecha_rechazo',
            'documento', 'observaciones', 'usuario', 'fecha_digitacion',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data, "Falta key en denegar-itse: {$key}");
        }
        $this->assertCount(8, array_keys($data));
        $this->assertEquals('ITSE', $data['tipo_autorizacion']);
        $this->assertEquals('RES-DENEGAR-ITSE-001', $data['documento']);

        // Insertar ITSE para que falle con 400
        DB::table('itse')->insert([
            'uuid' => '44444444-4444-4444-4444-444444444444',
            'numero_itse' => 999,
            'fecha_expedicion' => '2026-03-21',
            'nombre_comercial' => 'ITSE Denegado',
            'expediente_id' => $this->expedienteId,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/lf-itse/expedientes/{$this->expedienteId}/denegar-itse/", $payload)
            ->assertStatus(400);
    }

    public function test_autorizacion_improcedente_retorna_200_o_400_si_falta_tipo(): void
    {
        // Sin parámetro tipo -> 400
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/lf-itse/expedientes/{$this->expedienteId}/autorizacion-improcedente/")
            ->assertStatus(400)
            ->assertJson(['error' => "El parámetro 'tipo' es requerido ('LF' o 'ITSE')."]);

        // Primero crear autorización denegada
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/lf-itse/expedientes/{$this->expedienteId}/denegar-licencia/", [
                'fecha_rechazo' => '2026-03-20',
                'documento' => 'RES-LF-999',
                'observaciones' => 'Denegado por observaciones técnicas',
            ])->assertStatus(201);

        // Con tipo=LF -> 200 con 8 keys
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/lf-itse/expedientes/{$this->expedienteId}/autorizacion-improcedente/?tipo=LF");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotNull($data);
        $this->assertCount(8, array_keys($data));
        $this->assertEquals('LF', $data['tipo_autorizacion']);
    }
}
