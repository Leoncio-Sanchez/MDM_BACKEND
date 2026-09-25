<?php

declare(strict_types=1);

namespace Tests\Feature\Itse;

use App\Models\Itse;
use App\Models\Usuario as UsuarioModel;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ItseControllerTest extends TestCase
{
    private string $token;

    private int $itseId;

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

        if (! Schema::hasTable('niveles_riesgo')) {
            Schema::create('niveles_riesgo', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo')->nullable();
                $table->string('nombre');
                $table->boolean('esta_activo')->default(true);
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

        if (! Schema::hasTable('itse')) {
            Schema::create('itse', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid', 36)->nullable();
                $table->integer('numero_itse')->unique();
                $table->date('fecha_expedicion')->nullable();
                $table->date('fecha_solicitud_renovacion')->nullable();
                $table->date('fecha_caducidad')->nullable();
                $table->string('nombre_comercial', 200)->default('');
                $table->string('direccion', 300)->default('');
                $table->string('resolucion_numero', 100)->default('');
                $table->decimal('area', 10, 2)->default(0);
                $table->integer('capacidad_aforo')->default(0);
                $table->string('numero_recibo_pago', 50)->unique();
                $table->text('observaciones')->nullable();
                $table->boolean('se_puede_publicar')->default(false);
                $table->date('fecha_notificacion')->nullable();
                $table->dateTime('fecha_digitacion')->nullable();
                $table->integer('tipo_itse_id');
                $table->integer('expediente_id');
                $table->integer('itse_principal_id')->nullable();
                $table->integer('usuario_id')->nullable();
                $table->integer('nivel_riesgo_id');
                $table->integer('conductor_id');
                $table->integer('titular_id');
            });
        }

        if (! Schema::hasTable('itse_giros')) {
            Schema::create('itse_giros', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('itse_id');
                $table->integer('giro_id');
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        if (! Schema::hasTable('itse_estados')) {
            Schema::create('itse_estados', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('itse_id');
                $table->integer('estado_id');
                $table->date('fecha_estado');
                $table->string('documento', 100)->default('');
                $table->text('observaciones')->nullable();
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        if (! Schema::hasTable('itse_archivos')) {
            Schema::create('itse_archivos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid')->nullable();
                $table->integer('itse_id');
                $table->string('nombre_original')->nullable();
                $table->string('nombre_archivo')->nullable();
                $table->string('ruta_archivo')->nullable();
                $table->string('extension')->nullable();
                $table->bigInteger('tamanio_bytes')->default(0);
                $table->integer('usuario_id')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        DB::table('itse_estados')->truncate();
        DB::table('itse_giros')->truncate();
        DB::table('itse_archivos')->truncate();
        DB::table('itse')->truncate();
        DB::table('expedientes')->truncate();
        DB::table('autorizaciones_improcedentes')->truncate();
        DB::table('personas_documentos')->truncate();
        DB::table('personas')->truncate();
        DB::table('tipos_documento_identidad')->truncate();
        DB::table('niveles_riesgo')->truncate();
        DB::table('giros')->truncate();
        DB::table('estados')->truncate();
        DB::table('auth_user')->truncate();

        DB::table('auth_user')->insert([
            'id' => 1,
            'username' => 'admin_test',
            'password' => 'pbkdf2_sha256$260000$test$test',
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin@muni.gob.pe',
            'is_superuser' => true,
            'is_staff' => true,
            'is_active' => true,
        ]);

        $usuario = UsuarioModel::find(1);
        $authenticatedUser = new AuthenticatedUser(
            id: $usuario->id,
            username: $usuario->username,
            email: $usuario->email ?? 'admin@muni.gob.pe',
            role: 'ADMIN_MUNICIPALIDAD',
            ruc: null,
            passwordHash: $usuario->password,
        );
        $tokenService = app(TokenServiceInterface::class);
        $this->token = $tokenService->issue($authenticatedUser)->access;

        DB::table('estados')->insert([
            ['id' => 1, 'nombre' => 'ACTIVO', 'es_para_lf' => false, 'es_para_itse' => true, 'esta_activo' => true],
            ['id' => 2, 'nombre' => 'REVOCADO', 'es_para_lf' => false, 'es_para_itse' => true, 'esta_activo' => false],
        ]);

        DB::table('giros')->insert([
            ['id' => 1, 'ciiu_id' => 101, 'nombre' => 'Bodega', 'esta_activo' => true],
            ['id' => 2, 'ciiu_id' => 102, 'nombre' => 'Farmacia', 'esta_activo' => true],
        ]);

        DB::table('niveles_riesgo')->insert([
            ['id' => 1, 'codigo' => 'B', 'nombre' => 'BAJO', 'esta_activo' => true],
        ]);

        DB::table('personas')->insert([
            ['id' => 1, 'tipo_persona' => 'N', 'sexo' => 'M', 'apellido_paterno' => 'Perez', 'apellido_materno' => 'Gomez', 'nombres' => 'Juan', 'direccion' => 'Av. Central 123', 'user_id' => 1],
            ['id' => 2, 'tipo_persona' => 'N', 'sexo' => 'F', 'apellido_paterno' => 'Lopez', 'apellido_materno' => 'Diaz', 'nombres' => 'Maria', 'direccion' => 'Jr. Los Pinos 456', 'user_id' => 1],
        ]);

        DB::table('tipos_documento_identidad')->insert([
            ['id' => 1, 'codigo' => 'DNI', 'nombre' => 'Documento Nacional de Identidad'],
        ]);

        DB::table('personas_documentos')->insert([
            ['id' => 1, 'persona_id' => 1, 'tipo_documento_identidad_id' => 1, 'numero_documento' => '12345678'],
            ['id' => 2, 'persona_id' => 2, 'tipo_documento_identidad_id' => 1, 'numero_documento' => '87654321'],
        ]);

        DB::table('expedientes')->insert([
            ['id' => 1, 'numero_expediente' => 1001, 'fecha_recepcion' => '2024-03-15 10:00:00', 'fecha_ingreso' => '2024-03-15', 'usuario_id' => 1],
            ['id' => 2, 'numero_expediente' => 1002, 'fecha_recepcion' => '2024-04-10 11:00:00', 'fecha_ingreso' => '2024-04-10', 'usuario_id' => 1],
        ]);

        $this->itseId = DB::table('itse')->insertGetId([
            'uuid' => '00000000-0000-0000-0000-000000000001',
            'numero_itse' => 5001,
            'fecha_expedicion' => '2024-01-10',
            'fecha_solicitud_renovacion' => '2026-01-10',
            'fecha_caducidad' => '2026-01-10',
            'nombre_comercial' => 'Bodega El Sol',
            'direccion' => 'Av. Central 123',
            'resolucion_numero' => 'RES-2024-001',
            'area' => 85.50,
            'capacidad_aforo' => 30,
            'numero_recibo_pago' => 'REC-ITSE-001',
            'observaciones' => 'ITSE inicial',
            'se_puede_publicar' => true,
            'fecha_notificacion' => '2024-01-15',
            'fecha_digitacion' => '2024-01-10 12:00:00',
            'tipo_itse_id' => 1,
            'expediente_id' => 1,
            'itse_principal_id' => null,
            'usuario_id' => 1,
            'nivel_riesgo_id' => 1,
            'conductor_id' => 1,
            'titular_id' => 1,
        ]);

        DB::table('itse_giros')->insert([
            'itse_id' => $this->itseId,
            'giro_id' => 1,
            'usuario_id' => 1,
            'fecha_digitacion' => '2024-01-10 12:00:00',
        ]);

        DB::table('itse_estados')->insert([
            'itse_id' => $this->itseId,
            'estado_id' => 1,
            'fecha_estado' => '2024-01-10',
            'documento' => 'DOC-001',
            'observaciones' => 'Estado activo inicial',
            'usuario_id' => 1,
            'fecha_digitacion' => '2024-01-10 12:00:00',
        ]);
    }

    public function test_consulta_retorna_200_con_coleccion_itse(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/lf-itse/itse/consulta/');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertSame(5001, $data[0]['numero_itse']);
    }

    public function test_por_renovar_retorna_200_con_coleccion_itse(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/lf-itse/itse/por-renovar/');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertSame(5001, $data[0]['numero_itse']);
    }

    public function test_buscar_sin_parametros_falla_con_400(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/lf-itse/itse/buscar/');

        $response->assertStatus(400);
        $response->assertJson(['error' => "El parámetro 'filtro' es obligatorio."]);
    }

    public function test_buscar_sin_valor_falla_con_400(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/lf-itse/itse/buscar/?filtro=NUMERO');

        $response->assertStatus(400);
        $response->assertJson(['error' => "El parámetro 'valor' es obligatorio."]);
    }

    public function test_buscar_con_filtro_valido_retorna_200(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/lf-itse/itse/buscar/?filtro=NUMERO&valor=5001');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
    }

    public function test_verificar_expediente_sin_parametros_falla_con_400(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/lf-itse/itse/verificar-expediente/');

        $response->assertStatus(400);
        $response->assertJson(['error' => "El parámetro 'numero_expediente' es obligatorio."]);
    }

    public function test_verificar_expediente_con_parametros_retorna_200(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/lf-itse/itse/verificar-expediente/?numero_expediente=1002&anio=2024');

        $response->assertStatus(200);
        $response->assertJson([
            'se_puede_emitir_itse' => true,
            'expediente_id' => 2,
            'mensaje' => '',
        ]);
    }

    public function test_verificar_expediente_post_retorna_200(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/lf-itse/itse/verificar-expediente/', [
                'numero_expediente' => 1002,
                'anio' => 2024,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'se_puede_emitir_itse' => true,
            'expediente_id' => 2,
        ]);
    }

    public function test_show_retorna_200_con_26_keys(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/lf-itse/itse/{$this->itseId}/");

        $response->assertStatus(200);
        $data = $response->json();

        $expectedKeys = [
            'id', 'uuid', 'numero_itse', 'fecha_expedicion',
            'fecha_solicitud_renovacion', 'fecha_caducidad',
            'nombre_comercial', 'direccion', 'resolucion_numero',
            'area', 'capacidad_aforo', 'numero_recibo_pago',
            'observaciones', 'se_puede_publicar', 'fecha_notificacion',
            'fecha_digitacion', 'tipo_itse_id', 'expediente',
            'itse_principal', 'usuario', 'nivel_riesgo',
            'conductor', 'titular', 'archivos', 'historial_estados', 'giros',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data, "Falta la key: {$key}");
        }
        $this->assertCount(26, $data);
    }

    public function test_store_crea_itse_y_retorna_201(): void
    {
        $payload = [
            'numero_itse' => 6001,
            'fecha_expedicion' => '2024-05-01',
            'fecha_solicitud_renovacion' => '2026-05-01',
            'fecha_caducidad' => '2026-05-01',
            'nombre_comercial' => 'Farmacia San Juan',
            'direccion' => 'Jr. Los Pinos 456',
            'resolucion_numero' => 'RES-2024-002',
            'area' => 120.00,
            'capacidad_aforo' => 45,
            'numero_recibo_pago' => 'REC-ITSE-002',
            'observaciones' => 'ITSE nueva',
            'se_puede_publicar' => false,
            'tipo_itse_id' => 1,
            'expediente_id' => 2,
            'nivel_riesgo_id' => 1,
            'conductor_id' => 2,
            'titular_id' => 2,
            'giros' => [2],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/lf-itse/itse/', $payload);

        $response->assertStatus(201);
        $data = $response->json();
        $this->assertArrayHasKey('id', $data);

        $this->assertDatabaseHas('itse', ['numero_itse' => 6001]);
        $this->assertDatabaseHas('itse_giros', ['giro_id' => 2]);
    }

    public function test_update_modifica_itse_y_retorna_200(): void
    {
        $payload = [
            'nombre_comercial' => 'Bodega El Sol Actualizada',
            'direccion' => 'Av. Central 999',
            'area' => 90.00,
            'capacidad_aforo' => 35,
            'se_puede_publicar' => true,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/lf-itse/itse/{$this->itseId}/", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->itseId,
            'mensaje' => 'ITSE modificada correctamente.',
        ]);

        $this->assertDatabaseHas('itse', [
            'id' => $this->itseId,
            'nombre_comercial' => 'Bodega El Sol Actualizada',
        ]);
    }

    public function test_notificacion_actualiza_fecha_y_retorna_200(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/lf-itse/itse/{$this->itseId}/notificacion/", [
                'fecha_notificacion' => '2024-02-01',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['mensaje' => 'Fecha de notificación registrada correctamente.']);

        $itse = Itse::find($this->itseId);
        $this->assertNotNull($itse);
        $this->assertStringStartsWith('2024-02-01', (string) $itse->fecha_notificacion);
    }

    public function test_inactivar_registra_estado_y_retorna_201(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/lf-itse/itse/inactivar/', [
                'itse_id' => $this->itseId,
                'estado_id' => 2,
                'fecha_estado' => '2024-06-01',
                'documento' => 'RES-INAC-001',
                'observaciones' => 'Inactivado por cierre',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'mensaje' => 'Inactivación del ITSE registrada correctamente.',
        ]);

        $this->assertDatabaseHas('itse_estados', [
            'itse_id' => $this->itseId,
            'estado_id' => 2,
        ]);
    }

    public function test_estados_retorna_200(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/lf-itse/itse/{$this->itseId}/estados/");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_giros_retorna_200(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/lf-itse/itse/{$this->itseId}/giros/");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertSame('Bodega', $data[0]['nombre']);
    }

    public function test_inspectores_get_retorna_200_con_array_vacio(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/lf-itse/itse/{$this->itseId}/inspectores/");

        $response->assertStatus(200);
        $this->assertSame([], $response->json());
    }

    public function test_inspectores_post_retorna_201(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/lf-itse/itse/{$this->itseId}/inspectores/");

        $response->assertStatus(201);
        $response->assertJson(['mensaje' => 'Inspector asignado.']);
    }

    public function test_destroy_elimina_itse_y_retorna_204(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/lf-itse/itse/{$this->itseId}/");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('itse', ['id' => $this->itseId]);
    }

    public function test_endpoints_sin_autenticacion_retornan_401(): void
    {
        $this->getJson('/api/lf-itse/itse/consulta/')->assertStatus(401);
        $this->getJson('/api/lf-itse/itse/por-renovar/')->assertStatus(401);
        $this->getJson('/api/lf-itse/itse/buscar/')->assertStatus(401);
        $this->getJson('/api/lf-itse/itse/verificar-expediente/')->assertStatus(401);
        $this->postJson('/api/lf-itse/itse/', [])->assertStatus(401);
        $this->getJson("/api/lf-itse/itse/{$this->itseId}/")->assertStatus(401);
        $this->putJson("/api/lf-itse/itse/{$this->itseId}/", [])->assertStatus(401);
        $this->deleteJson("/api/lf-itse/itse/{$this->itseId}/")->assertStatus(401);
        $this->postJson('/api/lf-itse/itse/inactivar/', [])->assertStatus(401);
        $this->postJson("/api/lf-itse/itse/{$this->itseId}/notificacion/", [])->assertStatus(401);
        $this->getJson("/api/lf-itse/itse/{$this->itseId}/estados/")->assertStatus(401);
        $this->getJson("/api/lf-itse/itse/{$this->itseId}/giros/")->assertStatus(401);
        $this->getJson("/api/lf-itse/itse/{$this->itseId}/inspectores/")->assertStatus(401);
    }
}
