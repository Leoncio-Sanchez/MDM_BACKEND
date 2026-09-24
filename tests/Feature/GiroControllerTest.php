<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Giro;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class GiroControllerTest extends TestCase
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

        if (! Schema::hasTable('giros')) {
            Schema::create('giros', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('ciiu_id')->nullable();
                $table->string('nombre');
                $table->boolean('esta_activo')->default(true);
                $table->timestamp('fecha_digitacion')->nullable();
                $table->integer('usuario_id')->default(1);
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

    public function test_puede_listar_giros(): void
    {
        Giro::truncate();
        Giro::create([
            'ciiu_id' => 1234,
            'nombre' => 'Test Giro',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/giros');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_puede_obtener_giro(): void
    {
        Giro::truncate();
        $g = Giro::create([
            'ciiu_id' => 1234,
            'nombre' => 'Test Giro',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/giros/'.$g->id);

        $response->assertStatus(200);
        $this->assertSame(1234, $response->json('ciiu_id'));
    }

    public function test_rechaza_crear_giro_sin_auth(): void
    {
        $data = ['ciiu_id' => 5678, 'nombre' => 'Giro auth', 'esta_activo' => true];
        $response = $this->postJson('/api/lf-itse/giros', $data);
        $response->assertStatus(401);
    }

    public function test_puede_crear_giro_con_auth(): void
    {
        $data = ['ciiu_id' => 5678, 'nombre' => 'Giro auth', 'esta_activo' => true];
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/giros', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('giros', ['ciiu_id' => 5678, 'usuario_id' => 1]);
    }

    public function test_puede_actualizar_giro(): void
    {
        Giro::truncate();
        $g = Giro::create([
            'ciiu_id' => 1234,
            'nombre' => 'Test Giro',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $data = ['ciiu_id' => 9999, 'nombre' => 'Giro Upd', 'esta_activo' => false];
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/giros/'.$g->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('giros', ['ciiu_id' => 9999]);
    }

    public function test_puede_eliminar_giro(): void
    {
        Giro::truncate();
        $g = Giro::create([
            'ciiu_id' => 1234,
            'nombre' => 'Test Giro',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/giros/'.$g->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('giros', ['id' => $g->id]);
    }

    public function test_rechaza_actualizar_sin_auth(): void
    {
        Giro::truncate();
        $g = Giro::create([
            'ciiu_id' => 1234,
            'nombre' => 'Test Giro',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $response = $this->putJson('/api/lf-itse/giros/'.$g->id, []);
        $response->assertStatus(401);
    }
}
