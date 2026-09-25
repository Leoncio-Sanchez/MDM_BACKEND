<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UnidadOrganica;
use App\Services\DjangoJwtTokenService;
use App\Services\AuthenticatedUser;
use App\Models\Usuario as UsuarioModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class UnidadOrganicaControllerTest extends TestCase
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
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 200);
                $table->string('sigla', 50);
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

    public function test_rechaza_peticion_sin_autenticacion(): void
    {
        $response = $this->getJson('/api/lf-itse/unidades-organicas');
        $response->assertStatus(401);
    }

    public function test_lista_unidades_organicas(): void
    {
        UnidadOrganica::truncate();

        UnidadOrganica::create([
            'id' => 1,
            'codigo' => 'SGDEL',
            'nombre' => 'Sub Gerencia de Desarrollo Económico Local',
            'sigla' => 'SGDEL',
            'esta_activo' => true,
        ]);

        UnidadOrganica::create([
            'id' => 2,
            'codigo' => 'SGRD',
            'nombre' => 'Oficina de Gestión de Riesgos de Desastres',
            'sigla' => 'SGRD',
            'esta_activo' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/unidades-organicas');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(2, $data);
        // Debe ordenar por nombre ascendentemente (Oficina antes de Sub Gerencia)
        $this->assertSame('Oficina de Gestión de Riesgos de Desastres', $data[0]['nombre']);
        $this->assertSame('Sub Gerencia de Desarrollo Económico Local', $data[1]['nombre']);
        $this->assertSame('SGRD', $data[0]['codigo']);
        $this->assertTrue($data[0]['esta_activo']);
    }

    public function test_lista_unidades_organicas_con_trailing_slash(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/unidades-organicas/');

        $response->assertStatus(200);
    }

    public function test_puede_filtrar_por_esta_activo(): void
    {
        UnidadOrganica::truncate();

        UnidadOrganica::create([
            'id' => 1,
            'codigo' => 'ACT01',
            'nombre' => 'Activa',
            'sigla' => 'ACT',
            'esta_activo' => true,
        ]);

        UnidadOrganica::create([
            'id' => 2,
            'codigo' => 'INA01',
            'nombre' => 'Inactiva',
            'sigla' => 'INA',
            'esta_activo' => false,
        ]);

        $resActivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/unidades-organicas?esta_activo=true');
        $resActivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('ACT01', $resActivos->json('0.codigo'));

        $resInactivos = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/unidades-organicas?esta_activo=false');
        $resInactivos->assertStatus(200)->assertJsonCount(1);
        $this->assertSame('INA01', $resInactivos->json('0.codigo'));
    }
}
