<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NivelRiesgo;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class NivelRiesgoControllerTest extends TestCase
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

        if (! Schema::hasTable('niveles_riesgo')) {
            Schema::create('niveles_riesgo', function (Blueprint $table) {
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

    public function test_puede_listar_niveles_riesgo(): void
    {
        NivelRiesgo::truncate();
        NivelRiesgo::create(['codigo' => 'NRB', 'nombre' => 'RIESGO BAJO', 'esta_activo' => true]);
        NivelRiesgo::create(['codigo' => 'NRM', 'nombre' => 'RIESGO MEDIO', 'esta_activo' => false]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/niveles-riesgo');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_puede_filtrar_por_esta_activo(): void
    {
        NivelRiesgo::truncate();
        NivelRiesgo::create(['codigo' => 'NRB', 'nombre' => 'RIESGO BAJO', 'esta_activo' => true]);
        NivelRiesgo::create(['codigo' => 'NRM', 'nombre' => 'RIESGO MEDIO', 'esta_activo' => false]);

        $responseActivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/niveles-riesgo?esta_activo=true');
        $responseActivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('NRB', $responseActivos->json('0.codigo'));

        $responseInactivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/niveles-riesgo?esta_activo=false');
        $responseInactivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('NRM', $responseInactivos->json('0.codigo'));
    }

    public function test_puede_obtener_nivel_riesgo(): void
    {
        NivelRiesgo::truncate();
        $n = NivelRiesgo::create(['codigo' => 'NRB', 'nombre' => 'RIESGO BAJO', 'esta_activo' => true]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/niveles-riesgo/'.$n->id);

        $response->assertStatus(200);
        $this->assertSame('NRB', $response->json('codigo'));
        $this->assertSame('RIESGO BAJO', $response->json('nombre'));
        $this->assertTrue($response->json('esta_activo'));
    }

    public function test_rechaza_peticion_sin_auth(): void
    {
        $response = $this->getJson('/api/lf-itse/niveles-riesgo');
        $response->assertStatus(401);
    }

    public function test_puede_crear_nivel_riesgo(): void
    {
        NivelRiesgo::truncate();
        $data = ['codigo' => 'NEW', 'nombre' => 'NUEVO RIESGO', 'esta_activo' => true];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/niveles-riesgo', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('niveles_riesgo', ['codigo' => 'NEW', 'nombre' => 'NUEVO RIESGO']);
    }

    public function test_puede_actualizar_nivel_riesgo(): void
    {
        NivelRiesgo::truncate();
        $n = NivelRiesgo::create(['codigo' => 'NRB', 'nombre' => 'RIESGO BAJO', 'esta_activo' => true]);

        $data = ['codigo' => 'UPD', 'nombre' => 'RIESGO ACTUALIZADO', 'esta_activo' => false];
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/niveles-riesgo/'.$n->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('niveles_riesgo', ['id' => $n->id, 'codigo' => 'UPD', 'esta_activo' => false]);
    }

    public function test_puede_eliminar_nivel_riesgo(): void
    {
        NivelRiesgo::truncate();
        $n = NivelRiesgo::create(['codigo' => 'NRB', 'nombre' => 'RIESGO BAJO', 'esta_activo' => true]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/niveles-riesgo/'.$n->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('niveles_riesgo', ['id' => $n->id]);
    }
}
