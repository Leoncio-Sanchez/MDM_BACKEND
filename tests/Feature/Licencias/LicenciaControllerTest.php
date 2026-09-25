<?php

declare(strict_types=1);

namespace Tests\Feature\Licencias;

use App\Models\LicenciaFuncionamiento;
use App\Models\Usuario as UsuarioModel;
use App\Services\DjangoJwtTokenService;
use App\Services\AuthenticatedUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class LicenciaControllerTest extends TestCase
{
    private string $token;

    private int $licenciaId;

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

        if (! Schema::hasTable('estados')) {
            Schema::create('estados', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre');
                $table->boolean('es_para_lf')->default(false);
                $table->boolean('es_para_itse')->default(false);
                $table->boolean('esta_activo')->default(true);
            });
        }

        if (! Schema::hasTable('giros')) {
            Schema::create('giros', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('ciiu_id')->nullable();
                $table->string('nombre');
                $table->boolean('esta_activo')->default(true);
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        if (! Schema::hasTable('tipos_licencia')) {
            Schema::create('tipos_licencia', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo')->nullable();
                $table->string('nombre');
                $table->boolean('esta_activo')->default(true);
            });
        }

        if (! Schema::hasTable('niveles_riesgo')) {
            Schema::create('niveles_riesgo', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo')->nullable();
                $table->string('nombre');
                $table->boolean('esta_activo')->default(true);
            });
        }

        if (! Schema::hasTable('zonificaciones')) {
            Schema::create('zonificaciones', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo')->nullable();
                $table->string('nombre');
                $table->boolean('esta_activo')->default(true);
                $table->integer('usuario_id')->nullable();
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
                $table->integer('user_id')->default(1);
            });
        }

        if (! Schema::hasTable('tipos_documento_identidad')) {
            Schema::create('tipos_documento_identidad', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo', 10)->nullable();
                $table->string('nombre', 100)->nullable();
            });
        }

        if (! Schema::hasTable('personas_documentos')) {
            Schema::create('personas_documentos', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('persona_id');
                $table->integer('tipo_documento_identidad_id');
                $table->string('numero_documento', 50);
            });
        }

        if (! Schema::hasTable('expedientes')) {
            Schema::create('expedientes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('numero_expediente')->nullable();
                $table->dateTime('fecha_recepcion')->nullable();
                $table->date('fecha_ingreso')->nullable();
                $table->integer('usuario_id')->nullable();
            });
        }

        if (! Schema::hasTable('autorizaciones_improcedentes')) {
            Schema::create('autorizaciones_improcedentes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('expediente_id');
                $table->string('tipo_autorizacion', 10);
            });
        }

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
                $table->integer('numero_licencia')->nullable();
                $table->date('fecha_emision')->nullable();
                $table->integer('titular_id')->nullable();
                $table->integer('conductor_id')->nullable();
                $table->integer('licencia_principal_id')->nullable();
                $table->integer('expediente_id')->nullable();
                $table->integer('tipo_licencia_id')->nullable();
                $table->string('nombre_comercial')->nullable();
                $table->boolean('es_vigencia_indeterminada')->default(true);
                $table->date('fecha_inicio_vigencia')->nullable();
                $table->date('fecha_fin_vigencia')->nullable();
                $table->integer('nivel_riesgo_id')->nullable();
                $table->string('actividad')->nullable();
                $table->string('direccion')->nullable();
                $table->integer('hora_desde')->default(0);
                $table->integer('hora_hasta')->default(0);
                $table->string('resolucion_numero')->nullable();
                $table->integer('zonificacion_id')->nullable();
                $table->decimal('area', 18, 2)->default(0);
                $table->string('numero_recibo_pago')->nullable();
                $table->text('observaciones')->nullable();
                $table->boolean('se_puede_publicar')->default(true);
                $table->timestamp('fecha_notificacion')->nullable();
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
                $table->string('estado', 20)->default('EMITIDA');
            });
        }

        // Limpiar datos
        DB::table('licencias_funcionamiento_giros')->delete();
        DB::table('licencias_funcionamiento_estados')->delete();
        DB::table('licencias_funcionamiento_archivos')->delete();
        DB::table('licencias_funcionamiento')->delete();
        DB::table('estados')->delete();
        DB::table('giros')->delete();
        DB::table('tipos_licencia')->delete();
        DB::table('niveles_riesgo')->delete();
        DB::table('zonificaciones')->delete();
        DB::table('expedientes')->delete();
        DB::table('personas')->delete();
        DB::table('personas_documentos')->delete();
        DB::table('tipos_documento_identidad')->delete();
        DB::table('autorizaciones_improcedentes')->delete();
        UsuarioModel::query()->delete();

        // Usuario y token
        UsuarioModel::create([
            'id' => 1,
            'username' => 'admin',
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

        // Datos maestros relacionados
        DB::table('estados')->insert([
            ['id' => 1, 'nombre' => 'EMITIDA', 'es_para_lf' => true, 'es_para_itse' => false, 'esta_activo' => true],
            ['id' => 2, 'nombre' => 'REVOCADA', 'es_para_lf' => true, 'es_para_itse' => false, 'esta_activo' => false],
        ]);

        DB::table('giros')->insert([
            ['id' => 1, 'ciiu_id' => 4711, 'nombre' => 'BODEGA GENERAL', 'esta_activo' => true, 'usuario_id' => 1],
        ]);

        DB::table('tipos_licencia')->insert([
            ['id' => 1, 'codigo' => 'DEF', 'nombre' => 'DEFINITIVA', 'esta_activo' => true],
        ]);

        DB::table('niveles_riesgo')->insert([
            ['id' => 1, 'codigo' => 'R_BAJO', 'nombre' => 'RIESGO BAJO', 'esta_activo' => true],
        ]);

        DB::table('zonificaciones')->insert([
            ['id' => 1, 'codigo' => 'RDB', 'nombre' => 'RESIDENCIAL DENSIDAD BAJA', 'esta_activo' => true, 'usuario_id' => 1],
        ]);

        DB::table('personas')->insert([
            ['id' => 1, 'tipo_persona' => 'N', 'sexo' => 'M', 'apellido_paterno' => 'PEREZ', 'apellido_materno' => 'GOMEZ', 'nombres' => 'JUAN', 'user_id' => 1],
            ['id' => 2, 'tipo_persona' => 'N', 'sexo' => 'F', 'apellido_paterno' => 'LOPEZ', 'apellido_materno' => 'DIAZ', 'nombres' => 'MARIA', 'user_id' => 1],
        ]);

        DB::table('expedientes')->insert([
            [
                'id' => 1,
                'numero_expediente' => 1001,
                'fecha_ingreso' => '2024-01-10',
                'fecha_recepcion' => '2024-01-10 10:00:00',
                'usuario_id' => 1,
            ],
            [
                'id' => 2,
                'numero_expediente' => 2002,
                'fecha_ingreso' => '2024-02-10',
                'fecha_recepcion' => '2024-02-10 10:00:00',
                'usuario_id' => 1,
            ],
        ]);

        // Licencia base (asociada al expediente 1)
        $this->licenciaId = (int) DB::table('licencias_funcionamiento')->insertGetId([
            'uuid' => '00000000-0000-0000-0000-000000000001',
            'numero_licencia' => 12345,
            'fecha_emision' => '2024-02-01',
            'titular_id' => 1,
            'conductor_id' => 2,
            'expediente_id' => 1,
            'tipo_licencia_id' => 1,
            'nombre_comercial' => 'MI BODEGA TEST',
            'es_vigencia_indeterminada' => true,
            'nivel_riesgo_id' => 1,
            'actividad' => 'COMERCIO',
            'direccion' => 'AV PRINCIPAL 123',
            'hora_desde' => 8,
            'hora_hasta' => 20,
            'resolucion_numero' => 'RES-2024-001',
            'zonificacion_id' => 1,
            'area' => 50.00,
            'numero_recibo_pago' => 'REC-001',
            'observaciones' => 'NINGUNA',
            'se_puede_publicar' => true,
            'estado' => 'EMITIDA',
            'usuario_id' => 1,
        ]);

        DB::table('licencias_funcionamiento_estados')->insert([
            'licencia_funcionamiento_id' => $this->licenciaId,
            'estado_id' => 1,
            'fecha_estado' => '2024-02-01',
            'documento' => 'DOC-001',
            'observaciones' => 'ESTADO INICIAL',
            'usuario_id' => 1,
        ]);

        DB::table('licencias_funcionamiento_giros')->insert([
            'licencia_funcionamiento_id' => $this->licenciaId,
            'giro_id' => 1,
            'usuario_id' => 1,
        ]);
    }

    public function test_consulta_retorna_lista_de_licencias_con_estructura_correcta(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/licencias-funcionamiento/consulta/');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $keysEsperadas = [
            'id', 'uuid', 'expediente', 'tipo_licencia', 'numero_licencia', 'fecha_emision',
            'titular', 'conductor', 'licencia_principal', 'nombre_comercial',
            'es_vigencia_indeterminada', 'fecha_inicio_vigencia', 'fecha_fin_vigencia',
            'nivel_riesgo', 'actividad', 'direccion', 'hora_desde', 'hora_hasta',
            'resolucion_numero', 'zonificacion', 'area', 'numero_recibo_pago',
            'observaciones', 'se_puede_publicar', 'fecha_notificacion', 'usuario',
            'fecha_digitacion', 'archivos', 'historial_estados', 'giros',
        ];

        $this->assertCount(30, array_keys($data[0]));
        foreach ($keysEsperadas as $key) {
            $this->assertArrayHasKey($key, $data[0], "Falta key '{$key}' en LicenciaResource");
        }
    }

    public function test_buscar_sin_filtro_retorna_400_con_mensaje_django(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/licencias-funcionamiento/buscar/');

        $response->assertStatus(400);
        $this->assertSame(['error' => "El parámetro 'filtro' es obligatorio."], $response->json());
    }

    public function test_buscar_con_filtro_invalido_retorna_400(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/licencias-funcionamiento/buscar/?filtro=INVALIDO&valor=1');

        $response->assertStatus(400);
    }

    public function test_buscar_con_filtro_valido_retorna_200(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/licencias-funcionamiento/buscar/?filtro=NUMERO_LICENCIA&valor=12345');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $keysEsperadas = [
            'id', 'numero_licencia', 'fecha_emision', 'titular_id', 'conductor_id',
            'licencia_principal_id', 'nombre_comercial', 'es_vigencia_indeterminada',
            'fecha_inicio_vigencia', 'fecha_fin_vigencia', 'nivel_riesgo_id', 'actividad',
            'direccion', 'hora_desde', 'hora_hasta', 'resolucion_numero', 'zonificacion_id',
            'area', 'numero_recibo_pago', 'observaciones', 'se_puede_publicar',
            'fecha_notificacion', 'usuario_id', 'fecha_digitacion', 'numero_expediente',
            'fecha_recepcion', 'titular_nombre', 'titular_ruc', 'conductor_nombre',
            'esta_activo', 'tipo_licencia_nombre', 'zonificacion_nombre',
            'nivel_riesgo_nombre',
        ];

        foreach ($keysEsperadas as $key) {
            $this->assertArrayHasKey($key, $data[0], "Falta key '{$key}' en LicenciaListResource");
        }
    }

    public function test_verificar_expediente_sin_params_retorna_400_con_mensaje_django(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/licencias-funcionamiento/verificar-expediente/');

        $response->assertStatus(400);
        $this->assertSame(['error' => "El parámetro 'numero_expediente' es obligatorio."], $response->json());
    }

    public function test_verificar_expediente_retorna_200(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/licencias-funcionamiento/verificar-expediente/?numero_expediente=2002&anio=2024');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('se_puede_emitir_licencia', $data);
        $this->assertTrue($data['se_puede_emitir_licencia']);
    }

    public function test_show_retorna_licencia_con_30_keys(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/");

        $response->assertStatus(200);
        $data = $response->json();

        $keysEsperadas = [
            'id', 'uuid', 'expediente', 'tipo_licencia', 'numero_licencia', 'fecha_emision',
            'titular', 'conductor', 'licencia_principal', 'nombre_comercial',
            'es_vigencia_indeterminada', 'fecha_inicio_vigencia', 'fecha_fin_vigencia',
            'nivel_riesgo', 'actividad', 'direccion', 'hora_desde', 'hora_hasta',
            'resolucion_numero', 'zonificacion', 'area', 'numero_recibo_pago',
            'observaciones', 'se_puede_publicar', 'fecha_notificacion', 'usuario',
            'fecha_digitacion', 'archivos', 'historial_estados', 'giros',
        ];

        $this->assertCount(30, array_keys($data));
        foreach ($keysEsperadas as $key) {
            $this->assertArrayHasKey($key, $data, "Falta key '{$key}' en LicenciaResource");
        }
    }

    public function test_store_valida_campos_requeridos_retorna_400(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/licencias-funcionamiento/', []);

        $response->assertStatus(400);
    }

    public function test_store_crea_licencia_exitosamente(): void
    {
        $payload = [
            'numero_licencia' => 99999,
            'fecha_emision' => '2024-03-01',
            'titular_id' => 1,
            'conductor_id' => 2,
            'expediente_id' => 2,
            'tipo_licencia_id' => 1,
            'nombre_comercial' => 'NUEVA LICENCIA TEST',
            'es_vigencia_indeterminada' => true,
            'nivel_riesgo_id' => 1,
            'actividad' => 'SERVICIOS',
            'direccion' => 'CALLE REAL 456',
            'hora_desde' => 9,
            'hora_hasta' => 18,
            'resolucion_numero' => 'RES-2024-999',
            'zonificacion_id' => 1,
            'area' => '75.50',
            'numero_recibo_pago' => 'REC-999',
            'observaciones' => 'TEST CREACION',
            'se_puede_publicar' => false,
            'giros' => [1],
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/licencias-funcionamiento/', $payload);

        $response->assertStatus(201);
        $data = $response->json();
        $this->assertSame(99999, $data['numero_licencia']);
        $this->assertSame('NUEVA LICENCIA TEST', $data['nombre_comercial']);
    }

    public function test_update_modifica_licencia(): void
    {
        $payload = [
            'nombre_comercial' => 'BODEGA MODIFICADA',
            'area' => '80.00',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/", $payload);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertSame('BODEGA MODIFICADA', $data['nombre_comercial']);
    }

    public function test_inactivar_registra_estado_inactivo(): void
    {
        $payload = [
            'licencia_funcionamiento_id' => $this->licenciaId,
            'estado_id' => 2,
            'fecha_estado' => '2024-04-01',
            'documento' => 'RESOLUCION-REVOCACION-01',
            'observaciones' => 'Revocada por infracción',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/licencias-funcionamiento/inactivar/', $payload);

        $response->assertStatus(201);
        $data = $response->json();
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Inactivación de la licencia registrada correctamente.', $data['mensaje']);
    }

    public function test_notificar_actualiza_fecha_notificacion(): void
    {
        $payload = [
            'fecha_notificacion' => '2024-04-10',
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/notificacion/", $payload);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertSame('Fecha de notificación registrada correctamente.', $data['mensaje']);
    }

    public function test_estados_retorna_historial_de_estados(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/estados/");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertSame(1, $data[0]['estado_id']);
    }

    public function test_giros_retorna_giros_asociados(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/giros/");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertSame('4711', (string) $data[0]['ciiu_id']);
    }

    public function test_reporte_retorna_200(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/licencias-funcionamiento/reporte/?numero_licencia=12345');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
    }

    public function test_destroy_elimina_licencia(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/");

        $response->assertStatus(204);
        $this->assertNull(LicenciaFuncionamiento::find($this->licenciaId));
    }
}
