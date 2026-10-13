<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Tests\Feature;

use App\Models\User;
use App\Modules\TipoDocumentoIdentidad\Infrastructure\Persistence\Models\TipoDocumentoIdentidadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TipoDocumentoIdentidadTest extends TestCase
{
    use RefreshDatabase;

    private function getAuthUser(): User
    {
        $id = DB::table('auth_user')->insertGetId([
            'is_staff' => true,
            'username' => 'test_user',
            'password' => 'secret',
            'is_active' => true,
        ]);

        /** @var User $user */
        $user = User::find($id);
        return $user;
    }

    /** tipo_persona es opcional desde TD-006: sin él se listan todos (lo usa la pantalla de Personas). */
    public function test_lista_todos_sin_query_param(): void
    {
        DB::table('tipos_documento_identidad')->insert([
            ['codigo' => '01', 'nombre' => 'DNI', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false],
            ['codigo' => '06', 'nombre' => 'RUC', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true],
        ]);

        $this->comoUsuarioApi()->getJson('/api/lf-itse/tipos-documento-identidad')
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_rechaza_lista_con_query_param_invalido(): void
    {
        $this->comoUsuarioApi()->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=X')
            ->assertStatus(400)
            ->assertExactJson(['error' => "tipo_persona 'X' no válido. Opciones: N (natural), J (jurídica)."]);
    }

    public function test_puede_listar_naturales(): void
    {
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'DNI', 'nombre' => 'DNI Test', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false,
        ]);
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'RUC', 'nombre' => 'RUC Test', 'esta_activo' => true, 'es_para_natural' => false, 'es_para_juridica' => true,
        ]);

        $response = $this->comoUsuarioApi()->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=N');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $this->assertEquals('DNI', $response->json()[0]['codigo']);
    }

    public function test_puede_listar_juridicas(): void
    {
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'DNI', 'nombre' => 'DNI Test', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false,
        ]);
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'RUC', 'nombre' => 'RUC Test', 'esta_activo' => true, 'es_para_natural' => false, 'es_para_juridica' => true,
        ]);

        $response = $this->comoUsuarioApi()->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=J');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $this->assertEquals('RUC', $response->json()[0]['codigo']);
    }

    public function test_puede_crear_tipo(): void
    {
        $data = ['codigo' => 'NEW', 'nombre' => 'New Doc', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true];
        $response = $this->actingAs($this->getAuthUser(), 'api')->postJson('/api/lf-itse/tipos-documento-identidad', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('tipos_documento_identidad', ['codigo' => 'NEW']);
    }

    public function test_puede_actualizar_tipo(): void
    {
        $m = TipoDocumentoIdentidadModel::create([
            'codigo' => 'NEW', 'nombre' => 'New Doc', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true,
        ]);
        $data = ['codigo' => 'UPD', 'nombre' => 'Updated Doc', 'esta_activo' => false, 'es_para_natural' => true, 'es_para_juridica' => true];
        $response = $this->actingAs($this->getAuthUser(), 'api')->putJson('/api/lf-itse/tipos-documento-identidad/'.$m->id, $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('tipos_documento_identidad', ['codigo' => 'UPD']);
    }

    public function test_puede_eliminar_tipo(): void
    {
        $m = TipoDocumentoIdentidadModel::create([
            'codigo' => 'NEW', 'nombre' => 'New Doc', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true,
        ]);
        $response = $this->comoUsuarioApi()->deleteJson('/api/lf-itse/tipos-documento-identidad/'.$m->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tipos_documento_identidad', ['id' => $m->id]);
    }

    public function test_rechaza_todas_las_operaciones_sin_auth(): void
    {
        $url = '/api/lf-itse/tipos-documento-identidad';

        $this->getJson($url)->assertUnauthorized();
        $this->getJson($url . '/1')->assertUnauthorized();
        $this->postJson($url, [])->assertUnauthorized();
        $this->putJson($url . '/1', [])->assertUnauthorized();
        $this->deleteJson($url . '/1')->assertUnauthorized();
    }
}
