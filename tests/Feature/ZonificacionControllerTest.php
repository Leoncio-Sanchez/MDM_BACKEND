<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Zonificacion;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Models\Usuario as UsuarioModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ZonificacionControllerTest extends TestCase
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

        if (! Schema::hasTable('zonificaciones')) {
            Schema::create('zonificaciones', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo')->unique();
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

    public function test_puede_listar_zonificaciones(): void
    {
        Zonificacion::truncate();
        Zonificacion::create([
            'codigo' => 'ZDA',
            'nombre' => 'ZONA URBANA DE DENSIDAD ALTA',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);
        Zonificacion::create([
            'codigo' => 'ZDM',
            'nombre' => 'ZONA URBANA DE DENSIDAD MEDIA',
            'esta_activo' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/zonificaciones');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_puede_filtrar_por_esta_activo(): void
    {
        Zonificacion::truncate();
        Zonificacion::create([
            'codigo' => 'ZDA',
            'nombre' => 'ZONA URBANA DE DENSIDAD ALTA',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);
        Zonificacion::create([
            'codigo' => 'ZDM',
            'nombre' => 'ZONA URBANA DE DENSIDAD MEDIA',
            'esta_activo' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $responseActivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/zonificaciones?esta_activo=true');
        $responseActivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('ZDA', $responseActivos->json('0.codigo'));

        $responseInactivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/zonificaciones?esta_activo=false');
        $responseInactivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('ZDM', $responseInactivos->json('0.codigo'));
    }

    public function test_puede_obtener_zonificacion(): void
    {
        Zonificacion::truncate();
        $z = Zonificacion::create([
            'codigo' => 'ZDA',
            'nombre' => 'ZONA URBANA DE DENSIDAD ALTA',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/zonificaciones/'.$z->id);

        $response->assertStatus(200);
        $this->assertSame('ZDA', $response->json('codigo'));
        $this->assertSame('ZONA URBANA DE DENSIDAD ALTA', $response->json('nombre'));
        $this->assertTrue($response->json('esta_activo'));
        $this->assertSame(1, $response->json('usuario'));
    }

    public function test_rechaza_peticion_sin_auth(): void
    {
        $response = $this->getJson('/api/lf-itse/zonificaciones');
        $response->assertStatus(401);
    }

    public function test_puede_crear_zonificacion(): void
    {
        Zonificacion::truncate();
        $data = ['codigo' => 'NEW', 'nombre' => 'NUEVA ZONA', 'esta_activo' => true];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/zonificaciones', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('zonificaciones', ['codigo' => 'NEW', 'nombre' => 'NUEVA ZONA']);
    }

    public function test_puede_actualizar_zonificacion(): void
    {
        Zonificacion::truncate();
        $z = Zonificacion::create([
            'codigo' => 'ZDA',
            'nombre' => 'ZONA URBANA DE DENSIDAD ALTA',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $data = ['codigo' => 'UPD', 'nombre' => 'ZONA ACTUALIZADA', 'esta_activo' => false];
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/zonificaciones/'.$z->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('zonificaciones', ['id' => $z->id, 'codigo' => 'UPD', 'esta_activo' => false]);
    }

    public function test_puede_eliminar_zonificacion(): void
    {
        Zonificacion::truncate();
        $z = Zonificacion::create([
            'codigo' => 'ZDA',
            'nombre' => 'ZONA URBANA DE DENSIDAD ALTA',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/zonificaciones/'.$z->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('zonificaciones', ['id' => $z->id]);
    }
}
