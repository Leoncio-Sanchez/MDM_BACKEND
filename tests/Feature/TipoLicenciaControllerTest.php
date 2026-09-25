<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TipoLicencia;
use App\Services\DjangoJwtTokenService;
use App\Services\AuthenticatedUser;
use App\Models\Usuario as UsuarioModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class TipoLicenciaControllerTest extends TestCase
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

        if (! Schema::hasTable('tipos_licencia')) {
            Schema::create('tipos_licencia', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo')->unique();
                $table->string('nombre');
                $table->boolean('esta_activo')->default(true);
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
    }

    public function test_puede_listar_tipos_licencia(): void
    {
        TipoLicencia::truncate();
        TipoLicencia::create(['codigo' => 'EST', 'nombre' => 'ESTANDAR', 'esta_activo' => true]);
        TipoLicencia::create(['codigo' => 'CES', 'nombre' => 'CESIONARIO', 'esta_activo' => false]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-licencia');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_puede_filtrar_por_esta_activo(): void
    {
        TipoLicencia::truncate();
        TipoLicencia::create(['codigo' => 'EST', 'nombre' => 'ESTANDAR', 'esta_activo' => true]);
        TipoLicencia::create(['codigo' => 'CES', 'nombre' => 'CESIONARIO', 'esta_activo' => false]);

        $responseActivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-licencia?esta_activo=true');
        $responseActivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('EST', $responseActivos->json('0.codigo'));

        $responseInactivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-licencia?esta_activo=false');
        $responseInactivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('CES', $responseInactivos->json('0.codigo'));
    }

    public function test_puede_obtener_tipo_licencia(): void
    {
        TipoLicencia::truncate();
        $t = TipoLicencia::create(['codigo' => 'EST', 'nombre' => 'ESTANDAR', 'esta_activo' => true]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-licencia/'.$t->id);

        $response->assertStatus(200);
        $this->assertSame('EST', $response->json('codigo'));
        $this->assertSame('ESTANDAR', $response->json('nombre'));
        $this->assertTrue($response->json('esta_activo'));
    }

    public function test_rechaza_peticion_sin_auth(): void
    {
        $response = $this->getJson('/api/lf-itse/tipos-licencia');
        $response->assertStatus(401);
    }

    public function test_puede_crear_tipo_licencia(): void
    {
        TipoLicencia::truncate();
        $data = ['codigo' => 'NEW', 'nombre' => 'NUEVO TIPO', 'esta_activo' => true];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/tipos-licencia', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tipos_licencia', ['codigo' => 'NEW', 'nombre' => 'NUEVO TIPO']);
    }

    public function test_puede_actualizar_tipo_licencia(): void
    {
        TipoLicencia::truncate();
        $t = TipoLicencia::create(['codigo' => 'EST', 'nombre' => 'ESTANDAR', 'esta_activo' => true]);

        $data = ['codigo' => 'UPD', 'nombre' => 'UPDATED TIPO', 'esta_activo' => false];
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/tipos-licencia/'.$t->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tipos_licencia', ['id' => $t->id, 'codigo' => 'UPD', 'esta_activo' => false]);
    }

    public function test_puede_eliminar_tipo_licencia(): void
    {
        TipoLicencia::truncate();
        $t = TipoLicencia::create(['codigo' => 'EST', 'nombre' => 'ESTANDAR', 'esta_activo' => true]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/tipos-licencia/'.$t->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tipos_licencia', ['id' => $t->id]);
    }
}
