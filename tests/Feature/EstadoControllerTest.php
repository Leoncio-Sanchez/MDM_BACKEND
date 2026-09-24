<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Estado;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class EstadoControllerTest extends TestCase
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

        if (! Schema::hasTable('estados')) {
            Schema::create('estados', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre');
                $table->boolean('es_para_lf')->default(false);
                $table->boolean('es_para_itse')->default(false);
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

    public function test_rechaza_listar_sin_autenticacion(): void
    {
        $response = $this->getJson('/api/lf-itse/estados/');
        $response->assertUnauthorized();
    }

    public function test_lista_estados_paginados(): void
    {
        Estado::truncate();
        Estado::factory()->count(15)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/estados/?page=1');

        $response->assertOk()
            ->assertJsonStructure(['count', 'next', 'previous', 'results'])
            ->assertJsonPath('count', 15);
    }

    public function test_crea_un_estado(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/estados/', [
                'nombre' => 'Nuevo Test',
                'es_para_lf' => true,
                'es_para_itse' => false,
            ]);

        $response->assertCreated()
            ->assertJsonPath('nombre', 'Nuevo Test')
            ->assertJsonPath('esta_activo', false);
    }

    public function test_obtiene_un_estado_por_id(): void
    {
        $estado = Estado::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/lf-itse/estados/{$estado->id}");

        $response->assertOk()
            ->assertJsonPath('nombre', $estado->nombre);
    }

    public function test_actualiza_un_estado(): void
    {
        /** @var Estado $estado */
        $estado = Estado::factory()->create(['nombre' => 'Viejo', 'esta_activo' => false]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/lf-itse/estados/{$estado->id}", [
                'nombre' => 'Actualizado',
                'esta_activo' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('nombre', 'Actualizado')
            ->assertJsonPath('esta_activo', true);
    }

    public function test_elimina_un_estado(): void
    {
        $estado = Estado::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/lf-itse/estados/{$estado->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('estados', ['id' => $estado->id]);
    }

    public function test_inactivos_itse(): void
    {
        Estado::truncate();
        Estado::factory()->create(['nombre' => 'A', 'esta_activo' => false, 'es_para_itse' => true]);
        Estado::factory()->create(['nombre' => 'B', 'esta_activo' => true, 'es_para_itse' => true]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/estados/inactivos-itse');

        $response->assertOk();
        $this->assertCount(1, $response->json());
    }

    public function test_inactivos_lf(): void
    {
        Estado::truncate();
        Estado::factory()->create(['nombre' => 'A', 'esta_activo' => false, 'es_para_lf' => true]);
        Estado::factory()->create(['nombre' => 'B', 'esta_activo' => true, 'es_para_lf' => true]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/estados/inactivos-lf');

        $response->assertOk();
        $this->assertCount(1, $response->json());
    }
}
