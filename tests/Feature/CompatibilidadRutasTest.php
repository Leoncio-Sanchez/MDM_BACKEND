<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CompatibilidadRutasTest extends TestCase
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

        if (! Schema::hasTable('giros')) {
            Schema::create('giros', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ciiu_id');
                $table->string('nombre');
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

        if (! Schema::hasTable('licencias_funcionamiento')) {
            Schema::create('licencias_funcionamiento', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid')->nullable();
                $table->string('nombre_comercial')->nullable();
            });
        }

        UsuarioModel::truncate();
        $admin = UsuarioModel::create([
            'id' => 1,
            'username' => 'admin',
            'password' => 'hash',
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
            passwordHash: 'hash'
        );
        $this->token = $tokenService->issue($authUser)->access;
    }

    public function test_fecha_servidor_devuelve_fecha_actual(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/fecha-servidor/');

        $response->assertStatus(200)
            ->assertJsonStructure(['fecha']);
    }

    public function test_estados_inactivos_itse_filtra_correctamente(): void
    {
        DB::table('estados')->truncate();
        DB::table('estados')->insert([
            ['id' => 1, 'nombre' => 'ACTIVO', 'es_para_lf' => true, 'es_para_itse' => true, 'esta_activo' => true],
            ['id' => 2, 'nombre' => 'VENCIDO ITSE', 'es_para_lf' => false, 'es_para_itse' => true, 'esta_activo' => false],
            ['id' => 3, 'nombre' => 'CANCELADO LF', 'es_para_lf' => true, 'es_para_itse' => false, 'esta_activo' => false],
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/estados/inactivos-itse/');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertSame('VENCIDO ITSE', $data[0]['nombre']);
    }

    public function test_estados_inactivos_lf_filtra_correctamente(): void
    {
        DB::table('estados')->truncate();
        DB::table('estados')->insert([
            ['id' => 1, 'nombre' => 'ACTIVO', 'es_para_lf' => true, 'es_para_itse' => true, 'esta_activo' => true],
            ['id' => 2, 'nombre' => 'VENCIDO ITSE', 'es_para_lf' => false, 'es_para_itse' => true, 'esta_activo' => false],
            ['id' => 3, 'nombre' => 'CANCELADO LF', 'es_para_lf' => true, 'es_para_itse' => false, 'esta_activo' => false],
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/estados/inactivos-lf/');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertSame('CANCELADO LF', $data[0]['nombre']);
    }

    public function test_licencias_giros_retorna_giros_asociados(): void
    {
        DB::table('licencias_funcionamiento')->truncate();
        DB::table('giros')->truncate();
        DB::table('licencias_funcionamiento_giros')->truncate();

        DB::table('licencias_funcionamiento')->insert(['id' => 10, 'nombre_comercial' => 'Bodega Test']);
        DB::table('giros')->insert(['id' => 5, 'ciiu_id' => '4711', 'nombre' => 'VENTA AL POR MENOR']);
        DB::table('licencias_funcionamiento_giros')->insert([
            'id' => 1,
            'licencia_funcionamiento_id' => 10,
            'giro_id' => 5,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/licencias-funcionamiento/10/giros/');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertSame('4711', $data[0]['ciiu_id']);
        $this->assertSame('VENTA AL POR MENOR', $data[0]['nombre']);
    }
}
