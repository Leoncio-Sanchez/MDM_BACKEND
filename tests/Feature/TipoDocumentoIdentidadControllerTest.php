<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TipoDocumentoIdentidad;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class TipoDocumentoIdentidadControllerTest extends TestCase
{
    private string $token;

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
                $table->string('codigo')->unique();
                $table->string('nombre');
                $table->boolean('esta_activo')->default(true);
                $table->boolean('es_para_natural')->default(true);
                $table->boolean('es_para_juridica')->default(true);
            });
        }

        UsuarioModel::truncate();
        UsuarioModel::create([
            'id' => 1,
            'username' => 'admin',
            'password' => 'somehash',
            'is_superuser' => true,
            'is_staff' => true,
            'is_active' => true,
        ]);

        /** @var TokenServiceInterface $tokenService */
        $tokenService = app(TokenServiceInterface::class);
        $authUser = new AuthenticatedUser(
            id: 1,
            username: 'admin',
            email: 'admin@muni.gob.pe',
            role: 'ADMIN_MUNICIPALIDAD',
            ruc: null,
            passwordHash: 'somehash'
        );
        $this->token = $tokenService->issue($authUser)->access;
    }

    public function test_rechaza_sin_auth(): void
    {
        $response = $this->getJson('/api/lf-itse/tipos-documento-identidad');
        $response->assertStatus(401);
    }

    public function test_falla_sin_query_param_tipo_persona(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-documento-identidad');

        $response->assertStatus(400);
        $this->assertSame("El parámetro 'tipo_persona' es obligatorio.", $response->json('error'));
    }

    public function test_falla_con_tipo_persona_invalido(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=X');

        $response->assertStatus(400);
        $this->assertSame("tipo_persona 'X' no válido. Opciones: N (natural), J (jurídica).", $response->json('error'));
    }

    public function test_puede_listar_para_persona_natural(): void
    {
        TipoDocumentoIdentidad::truncate();
        TipoDocumentoIdentidad::create(['codigo' => '01', 'nombre' => 'DNI', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false]);
        TipoDocumentoIdentidad::create(['codigo' => '06', 'nombre' => 'RUC', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true]);
        TipoDocumentoIdentidad::create(['codigo' => '99', 'nombre' => 'SOLO JURIDICA', 'esta_activo' => true, 'es_para_natural' => false, 'es_para_juridica' => true]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=N');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_puede_listar_para_persona_juridica(): void
    {
        TipoDocumentoIdentidad::truncate();
        TipoDocumentoIdentidad::create(['codigo' => '01', 'nombre' => 'DNI', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false]);
        TipoDocumentoIdentidad::create(['codigo' => '06', 'nombre' => 'RUC', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true]);
        TipoDocumentoIdentidad::create(['codigo' => '99', 'nombre' => 'SOLO JURIDICA', 'esta_activo' => true, 'es_para_natural' => false, 'es_para_juridica' => true]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=J');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_puede_obtener_tipo_documento(): void
    {
        TipoDocumentoIdentidad::truncate();
        $t = TipoDocumentoIdentidad::create(['codigo' => '01', 'nombre' => 'DNI', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-documento-identidad/'.$t->id);

        $response->assertStatus(200);
        $this->assertSame('01', $response->json('codigo'));
        $this->assertSame('DNI', $response->json('nombre'));
        $this->assertTrue($response->json('es_para_natural'));
        $this->assertFalse($response->json('es_para_juridica'));
    }

    public function test_puede_crear_tipo_documento(): void
    {
        TipoDocumentoIdentidad::truncate();
        $data = [
            'codigo' => '07',
            'nombre' => 'PASAPORTE',
            'esta_activo' => true,
            'es_para_natural' => true,
            'es_para_juridica' => false,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/tipos-documento-identidad', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tipos_documento_identidad', ['codigo' => '07', 'nombre' => 'PASAPORTE']);
    }

    public function test_puede_actualizar_tipo_documento(): void
    {
        TipoDocumentoIdentidad::truncate();
        $t = TipoDocumentoIdentidad::create(['codigo' => '01', 'nombre' => 'DNI', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false]);

        $data = ['codigo' => '01', 'nombre' => 'DNI ACTUALIZADO', 'esta_activo' => false];
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/tipos-documento-identidad/'.$t->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tipos_documento_identidad', ['id' => $t->id, 'nombre' => 'DNI ACTUALIZADO', 'esta_activo' => false]);
    }

    public function test_puede_eliminar_tipo_documento(): void
    {
        TipoDocumentoIdentidad::truncate();
        $t = TipoDocumentoIdentidad::create(['codigo' => '01', 'nombre' => 'DNI', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/tipos-documento-identidad/'.$t->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tipos_documento_identidad', ['id' => $t->id]);
    }
}
