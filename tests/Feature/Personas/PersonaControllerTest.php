<?php

declare(strict_types=1);

namespace Tests\Feature\Personas;

use App\Models\Persona;
use App\Models\PersonaDocumento;
use App\Models\TipoDocumentoIdentidad;
use App\Models\Usuario as UsuarioModel;
use App\Services\DjangoJwtTokenService;
use App\Services\AuthenticatedUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PersonaControllerTest extends TestCase
{
    private string $token;

    private int $personaId;

    private int $tipoDniId;

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

        if (! Schema::hasTable('personas')) {
            Schema::create('personas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('tipo_persona', 1)->default('N');
                $table->string('sexo', 1)->default('X');
                $table->string('apellido_paterno', 50)->nullable();
                $table->string('apellido_materno', 50)->nullable();
                $table->string('nombres', 100);
                $table->string('direccion', 250);
                $table->string('distrito', 100);
                $table->string('provincia', 100);
                $table->string('departamento', 100);
                $table->string('telefono', 30)->nullable();
                $table->string('correo_electronico', 150)->nullable();
                $table->dateTime('fecha_creacion');
                $table->dateTime('fecha_actualizacion');
                $table->unsignedInteger('user_id')->default(1);
            });
        }

        if (! Schema::hasTable('personas_documentos')) {
            Schema::create('personas_documentos', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('persona_id');
                $table->unsignedInteger('tipo_documento_identidad_id');
                $table->string('numero_documento', 20);
            });
        }

        if (! Schema::hasTable('expedientes')) {
            Schema::create('expedientes', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('solicitante_id')->nullable();
                $table->unsignedInteger('representante_id')->nullable();
            });
        }

        if (! Schema::hasTable('licencias_funcionamiento')) {
            Schema::create('licencias_funcionamiento', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('titular_id')->nullable();
                $table->unsignedInteger('conductor_id')->nullable();
            });
        }

        if (! Schema::hasTable('itse')) {
            Schema::create('itse', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('titular_id')->nullable();
                $table->unsignedInteger('conductor_id')->nullable();
            });
        }

        $user = UsuarioModel::firstOrCreate(
            ['username' => 'admin_test_personas'],
            [
                'password' => bcrypt('password'),
                'email' => 'admin@personas.com',
                'is_superuser' => true,
                'is_staff' => true,
                'is_active' => true,
            ]
        );

        $authUser = new AuthenticatedUser(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            role: 'ADMIN_MUNICIPALIDAD',
            ruc: null,
            passwordHash: $user->password
        );
        $this->token = app(DjangoJwtTokenService::class)->issue($authUser)->access;

        $tipoDni = TipoDocumentoIdentidad::firstOrCreate(
            ['codigo' => '01'],
            [
                'nombre' => 'DNI',
                'esta_activo' => true,
                'es_para_natural' => true,
                'es_para_juridica' => false,
            ]
        );
        $this->tipoDniId = (int) $tipoDni->id;

        $persona = Persona::create([
            'tipo_persona' => 'N',
            'sexo' => 'M',
            'apellido_paterno' => 'GARCIA',
            'apellido_materno' => 'PEREZ',
            'nombres' => 'JUAN CARLOS',
            'direccion' => 'AV PRINCIPAL 123',
            'distrito' => 'PIURA',
            'provincia' => 'PIURA',
            'departamento' => 'PIURA',
            'telefono' => '999888777',
            'correo_electronico' => 'juan.garcia@test.com',
            'fecha_creacion' => now()->toDateTimeString(),
            'fecha_actualizacion' => now()->toDateTimeString(),
            'user_id' => $user->id,
        ]);
        $this->personaId = (int) $persona->id;

        PersonaDocumento::create([
            'persona_id' => $this->personaId,
            'tipo_documento_identidad_id' => $this->tipoDniId,
            'numero_documento' => '12345678',
        ]);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/lf-itse/personas')
            ->assertStatus(401);

        $this->postJson('/api/lf-itse/personas', [])
            ->assertStatus(401);

        $this->getJson("/api/lf-itse/personas/{$this->personaId}")
            ->assertStatus(401);

        $this->putJson("/api/lf-itse/personas/{$this->personaId}", [])
            ->assertStatus(401);

        $this->deleteJson("/api/lf-itse/personas/{$this->personaId}")
            ->assertStatus(401);

        $this->getJson('/api/lf-itse/personas/sexos')
            ->assertStatus(401);

        $this->getJson('/api/lf-itse/personas/buscar')
            ->assertStatus(401);

        $this->getJson("/api/lf-itse/personas/{$this->personaId}/documentos")
            ->assertStatus(401);
    }

    public function test_listar_personas(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/personas/');

        $response->assertStatus(200)
            ->assertJsonIsArray()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'documentos',
                    'tipo_persona',
                    'sexo',
                    'apellido_paterno',
                    'apellido_materno',
                    'nombres',
                    'direccion',
                    'distrito',
                    'provincia',
                    'departamento',
                    'telefono',
                    'correo_electronico',
                    'fecha_creacion',
                    'fecha_actualizacion',
                    'user',
                ],
            ]);
    }

    public function test_obtener_persona_existente(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson("/api/lf-itse/personas/{$this->personaId}/");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->personaId,
                'apellido_paterno' => 'GARCIA',
                'nombres' => 'JUAN CARLOS',
            ])
            ->assertJsonStructure([
                'id',
                'documentos' => [
                    '*' => [
                        'id',
                        'numero_documento',
                        'persona',
                        'tipo_documento_identidad',
                    ],
                ],
            ]);
    }

    public function test_obtener_persona_inexistente(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/personas/99999/');

        $response->assertStatus(404)
            ->assertJson(['error' => 'No Persona matches the given query.']);
    }

    public function test_crear_persona_natural(): void
    {
        $payload = [
            'tipo_persona' => 'N',
            'sexo' => 'F',
            'apellido_paterno' => 'LOPEZ',
            'apellido_materno' => 'DIAZ',
            'nombres' => 'MARIA ELENA',
            'direccion' => 'CALLE REAL 456',
            'distrito' => 'CASTILLA',
            'provincia' => 'PIURA',
            'departamento' => 'PIURA',
            'telefono' => '987654321',
            'correo_electronico' => 'maria.lopez@test.com',
            'documentos' => [
                [
                    'tipo_documento_identidad_id' => $this->tipoDniId,
                    'numero_documento' => '87654321',
                ],
            ],
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/lf-itse/personas/', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'tipo_persona' => 'N',
                'apellido_paterno' => 'LOPEZ',
                'nombres' => 'MARIA ELENA',
            ])
            ->assertJsonCount(1, 'documentos');

        $this->assertDatabaseHas('personas', [
            'apellido_paterno' => 'LOPEZ',
            'nombres' => 'MARIA ELENA',
        ]);
        $this->assertDatabaseHas('personas_documentos', [
            'numero_documento' => '87654321',
        ]);
    }

    public function test_crear_persona_juridica(): void
    {
        $payload = [
            'tipo_persona' => 'J',
            'sexo' => 'X',
            'apellido_paterno' => 'IGNORED',
            'apellido_materno' => 'IGNORED',
            'nombres' => 'EMPRESA COMERCIAL SAC',
            'direccion' => 'AV COMERCIO 789',
            'distrito' => 'PIURA',
            'provincia' => 'PIURA',
            'departamento' => 'PIURA',
            'documentos' => [
                [
                    'tipo_documento_identidad_id' => $this->tipoDniId,
                    'numero_documento' => '20123456789',
                ],
            ],
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/lf-itse/personas/', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'tipo_persona' => 'J',
                'apellido_paterno' => null,
                'apellido_materno' => null,
                'nombres' => 'EMPRESA COMERCIAL SAC',
            ]);
    }

    public function test_crear_persona_documento_duplicado(): void
    {
        $payload = [
            'tipo_persona' => 'N',
            'sexo' => 'M',
            'apellido_paterno' => 'OTRO',
            'nombres' => 'PEDRO',
            'direccion' => 'DIR 1',
            'distrito' => 'PIURA',
            'provincia' => 'PIURA',
            'departamento' => 'PIURA',
            'documentos' => [
                [
                    'tipo_documento_identidad_id' => $this->tipoDniId,
                    'numero_documento' => '12345678', // Ya asignado en setUp
                ],
            ],
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/lf-itse/personas/', $payload);

        $response->assertStatus(409)
            ->assertJsonStructure(['error']);
    }

    public function test_actualizar_persona(): void
    {
        $payload = [
            'nombres' => 'JUAN CARLOS MODIFICADO',
            'direccion' => 'NUEVA DIRECCION 999',
            'documentos' => [
                [
                    'tipo_documento_identidad_id' => $this->tipoDniId,
                    'numero_documento' => '12345678',
                ],
            ],
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->putJson("/api/lf-itse/personas/{$this->personaId}/", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->personaId,
                'nombres' => 'JUAN CARLOS MODIFICADO',
                'direccion' => 'NUEVA DIRECCION 999',
            ]);
    }

    public function test_eliminar_persona(): void
    {
        $persona = Persona::create([
            'tipo_persona' => 'N',
            'sexo' => 'M',
            'nombres' => 'BORRAR',
            'direccion' => 'DIR',
            'distrito' => 'D',
            'provincia' => 'P',
            'departamento' => 'D',
            'fecha_creacion' => now()->toDateTimeString(),
            'fecha_actualizacion' => now()->toDateTimeString(),
            'user_id' => 1,
        ]);
        PersonaDocumento::create([
            'persona_id' => $persona->id,
            'tipo_documento_identidad_id' => $this->tipoDniId,
            'numero_documento' => '99887766',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->delete("/api/lf-itse/personas/{$persona->id}/");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('personas', ['id' => $persona->id]);
        $this->assertDatabaseMissing('personas_documentos', ['persona_id' => $persona->id]);
    }

    public function test_eliminar_persona_inexistente(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/lf-itse/personas/99999/');

        $response->assertStatus(404)
            ->assertJson(['error' => 'No Persona matches the given query.']);
    }

    public function test_sexos_catalogo(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/personas/sexos/');

        $response->assertStatus(200)
            ->assertJson([
                ['value' => 'M', 'label' => 'Masculino'],
                ['value' => 'F', 'label' => 'Femenino'],
                ['value' => 'X', 'label' => 'Prefiero no decirlo'],
            ]);
    }

    public function test_listar_documentos_persona(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson("/api/lf-itse/personas/{$this->personaId}/documentos/");

        $response->assertStatus(200)
            ->assertJsonIsArray()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'persona_id',
                    'tipo_documento_identidad_id',
                    'numero_documento',
                    'tipos_documento_identidad_codigo',
                    'tipos_documento_identidad_nombre',
                ],
            ]);
    }

    public function test_listar_documentos_persona_inexistente(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/personas/99999/documentos/');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Persona no encontrada.']);
    }

    public function test_buscar_sin_filtro(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/personas/buscar/');

        $response->assertStatus(400)
            ->assertJson(['error' => "El parámetro 'filtro' es obligatorio."]);
    }

    public function test_buscar_sin_valor(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/personas/buscar/?filtro=NOMBRE');

        $response->assertStatus(400)
            ->assertJson(['error' => "El parámetro 'valor' es obligatorio."]);
    }

    public function test_buscar_filtro_invalido(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/personas/buscar/?filtro=INVALIDO&valor=123');

        $response->assertStatus(400)
            ->assertJson(['error' => "Filtro 'INVALIDO' no válido. Opciones: NOMBRE, DOCUMENTO, ID"]);
    }
}
