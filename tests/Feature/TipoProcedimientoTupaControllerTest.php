<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TipoProcedimientoTupa;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Models\Usuario as UsuarioModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class TipoProcedimientoTupaControllerTest extends TestCase
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

        if (! Schema::hasTable('unidades_organicas')) {
            Schema::create('unidades_organicas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo')->unique();
                $table->string('nombre');
                $table->boolean('esta_activo')->default(true);
            });
        }

        if (! Schema::hasTable('tipos_procedimiento_tupa')) {
            Schema::create('tipos_procedimiento_tupa', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 250);
                $table->integer('plazo_atencion_dias')->default(0);
                $table->integer('dias_alerta_vencimiento')->default(0);
                $table->boolean('esta_activo')->default(true);
                $table->boolean('requiere_lf')->default(false);
                $table->boolean('requiere_itse')->default(false);
                $table->timestamp('fecha_digitacion')->nullable();
                $table->integer('unidad_organica_id')->default(1);
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

    public function test_puede_listar_tipos_procedimiento_tupa(): void
    {
        TipoProcedimientoTupa::truncate();
        TipoProcedimientoTupa::create([
            'codigo' => 'B01',
            'nombre' => 'B Procedimiento',
            'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 2,
            'esta_activo' => true,
            'requiere_lf' => false,
            'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'unidad_organica_id' => 1,
            'usuario_id' => 1,
        ]);
        TipoProcedimientoTupa::create([
            'codigo' => 'A01',
            'nombre' => 'A Procedimiento',
            'plazo_atencion_dias' => 5,
            'dias_alerta_vencimiento' => 1,
            'esta_activo' => false,
            'requiere_lf' => true,
            'requiere_itse' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'unidad_organica_id' => 1,
            'usuario_id' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-procedimiento-tupa');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        // Debe ordenar por nombre ascendentemente
        $this->assertSame('A Procedimiento', $response->json('0.nombre'));
        $this->assertSame('B Procedimiento', $response->json('1.nombre'));
    }

    public function test_puede_filtrar_por_esta_activo(): void
    {
        TipoProcedimientoTupa::truncate();
        TipoProcedimientoTupa::create([
            'codigo' => 'T01',
            'nombre' => 'Activo Tupa',
            'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 2,
            'esta_activo' => true,
            'requiere_lf' => false,
            'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'unidad_organica_id' => 1,
            'usuario_id' => 1,
        ]);
        TipoProcedimientoTupa::create([
            'codigo' => 'T02',
            'nombre' => 'Inactivo Tupa',
            'plazo_atencion_dias' => 5,
            'dias_alerta_vencimiento' => 1,
            'esta_activo' => false,
            'requiere_lf' => false,
            'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'unidad_organica_id' => 1,
            'usuario_id' => 1,
        ]);

        $responseActivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-procedimiento-tupa?esta_activo=true');
        $responseActivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('T01', $responseActivos->json('0.codigo'));

        $responseInactivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-procedimiento-tupa?esta_activo=false');
        $responseInactivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('T02', $responseInactivos->json('0.codigo'));
    }

    public function test_puede_obtener_tipo_procedimiento_tupa(): void
    {
        TipoProcedimientoTupa::truncate();
        $tipo = TipoProcedimientoTupa::create([
            'codigo' => 'T01',
            'nombre' => 'Test Tupa',
            'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 2,
            'esta_activo' => true,
            'requiere_lf' => true,
            'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'unidad_organica_id' => 1,
            'usuario_id' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/tipos-procedimiento-tupa/'.$tipo->id);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $tipo->id,
            'codigo' => 'T01',
            'nombre' => 'Test Tupa',
            'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 2,
            'esta_activo' => true,
            'requiere_lf' => true,
            'requiere_itse' => false,
            'unidad_organica' => 1,
            'usuario' => 1,
        ]);
        $this->assertArrayNotHasKey('monto', $response->json());
    }

    public function test_rechaza_peticion_sin_auth(): void
    {
        $response = $this->getJson('/api/lf-itse/tipos-procedimiento-tupa');
        $response->assertStatus(401);
    }

    public function test_puede_crear_tipo_procedimiento_tupa(): void
    {
        TipoProcedimientoTupa::truncate();
        $data = [
            'codigo' => 'new01',
            'nombre' => 'Nuevo Procedimiento',
            'plazo_atencion_dias' => 15,
            'dias_alerta_vencimiento' => 3,
            'esta_activo' => true,
            'requiere_lf' => true,
            'requiere_itse' => false,
            'unidad_organica_id' => 1,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/tipos-procedimiento-tupa', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tipos_procedimiento_tupa', [
            'codigo' => 'NEW01',
            'nombre' => 'Nuevo Procedimiento',
            'usuario_id' => 1,
        ]);
        $this->assertSame('NEW01', $response->json('codigo'));
    }

    public function test_puede_actualizar_tipo_procedimiento_tupa(): void
    {
        TipoProcedimientoTupa::truncate();
        $tipo = TipoProcedimientoTupa::create([
            'codigo' => 'T01',
            'nombre' => 'Original Tupa',
            'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 2,
            'esta_activo' => true,
            'requiere_lf' => false,
            'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'unidad_organica_id' => 1,
            'usuario_id' => 1,
        ]);

        $data = [
            'codigo' => 't01_mod',
            'nombre' => 'Modificado Tupa',
            'plazo_atencion_dias' => 20,
            'esta_activo' => false,
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/tipos-procedimiento-tupa/'.$tipo->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tipos_procedimiento_tupa', [
            'id' => $tipo->id,
            'codigo' => 'T01_MOD',
            'nombre' => 'Modificado Tupa',
            'plazo_atencion_dias' => 20,
            'esta_activo' => false,
        ]);
    }

    public function test_puede_eliminar_tipo_procedimiento_tupa(): void
    {
        TipoProcedimientoTupa::truncate();
        $tipo = TipoProcedimientoTupa::create([
            'codigo' => 'T01',
            'nombre' => 'Para Eliminar',
            'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 2,
            'esta_activo' => true,
            'requiere_lf' => false,
            'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'unidad_organica_id' => 1,
            'usuario_id' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/tipos-procedimiento-tupa/'.$tipo->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tipos_procedimiento_tupa', ['id' => $tipo->id]);
    }
}
