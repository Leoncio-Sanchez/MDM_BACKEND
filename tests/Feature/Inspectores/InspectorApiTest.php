<?php

declare(strict_types=1);

namespace Tests\Feature\Inspectores;

use App\Models\Usuario;
use App\Services\DjangoJwtTokenService;
use App\Services\AuthenticatedUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class InspectorApiTest extends TestCase
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

        if (! Schema::hasTable('usuarios_perfiles')) {
            Schema::create('usuarios_perfiles', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->boolean('expedientes')->default(false);
                $table->boolean('licencias')->default(false);
                $table->boolean('itse')->default(false);
                $table->boolean('admin')->default(false);
                $table->integer('user_id_digitador')->default(1);
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        Usuario::truncate();

        Usuario::create([
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

    public function test_listar_inspectores_devuelve_200(): void
    {
        Usuario::create([
            'username' => 'inspector_carlos',
            'first_name' => 'Carlos',
            'last_name' => 'Sanchez',
            'is_active' => true,
            'password' => 'somehash',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/inspectores/');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
        $this->assertGreaterThanOrEqual(1, count($response->json()));
    }

    public function test_index_filtra_solo_is_active_true(): void
    {
        Usuario::create([
            'username' => 'inspector_inactivo',
            'first_name' => 'Inactivo',
            'last_name' => 'User',
            'is_active' => false,
            'password' => 'somehash',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/inspectores/');

        $response->assertStatus(200);
        $usernames = collect($response->json())->pluck('username')->all();
        $this->assertNotContains('inspector_inactivo', $usernames);
    }

    public function test_crear_inspector_devuelve_201(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/inspectores/', [
                'username' => 'inspector_nuevo',
                'password' => 'clave1234',
                'first_name' => 'Roberto',
                'last_name' => 'Gomez',
                'email' => 'roberto@muni.gob.pe',
            ]);

        $response->assertStatus(201);
        $this->assertSame('inspector_nuevo', $response->json('username'));
        $this->assertSame('Roberto Gomez', $response->json('nombre_completo'));
    }

    public function test_obtener_inspector_por_id(): void
    {
        $insp = Usuario::create([
            'username' => 'inspector_detalle',
            'first_name' => 'Pedro',
            'password' => 'somehash',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/inspectores/'.$insp->id.'/');

        $response->assertStatus(200);
        $this->assertSame('inspector_detalle', $response->json('username'));
    }

    public function test_obtener_inspector_no_encontrado_devuelve_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/inspectores/999999/');

        $response->assertStatus(404);
        $response->assertJson(['detail' => 'No encontrado.']);
    }

    public function test_buscar_inspectores(): void
    {
        Usuario::create([
            'username' => 'inspector_busqueda',
            'first_name' => 'Mariana',
            'password' => 'somehash',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/inspectores/buscar/?busqueda=Mariana');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertSame('inspector_busqueda', $response->json()[0]['username']);
    }

    public function test_buscar_inspectores_con_query_param_busqueda(): void
    {
        Usuario::create([
            'username' => 'inspector_search_query',
            'first_name' => 'InspectorSearch',
            'password' => 'somehash',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/inspectores/buscar?busqueda=InspectorSearch');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertSame('inspector_search_query', $response->json()[0]['username']);
    }

    public function test_actualizar_inspector(): void
    {
        $insp = Usuario::create([
            'username' => 'inspector_mod',
            'first_name' => 'Luis',
            'password' => 'somehash',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/inspectores/'.$insp->id.'/', [
                'first_name' => 'Luis Alberto',
                'last_name' => 'Ramirez',
            ]);

        $response->assertStatus(200);
        $this->assertSame('Luis Alberto Ramirez', $response->json('nombre_completo'));
    }

    public function test_actualizar_inspector_no_encontrado_devuelve_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/inspectores/999999/', [
                'first_name' => 'Non Existent',
            ]);

        $response->assertStatus(404);
        $response->assertJson(['detail' => 'No encontrado.']);
    }

    public function test_eliminar_inspector(): void
    {
        $insp = Usuario::create([
            'username' => 'inspector_eliminar',
            'password' => 'somehash',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/inspectores/'.$insp->id.'/');

        $response->assertStatus(204);
        $this->assertNull(Usuario::find($insp->id));
    }

    public function test_eliminar_inspector_no_encontrado_devuelve_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/inspectores/999999/');

        $response->assertStatus(404);
        $response->assertJson(['detail' => 'No encontrado.']);
    }
}
