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
            'username' => 'test_user',
            'password' => 'secret',
            'is_active' => true,
        ]);

        /** @var User $user */
        $user = User::find($id);
        return $user;
    }

    public function test_rechaza_lista_sin_query_param(): void
    {
        $response = $this->getJson('/api/lf-itse/tipos-documento-identidad');
        $response->assertStatus(400)
            ->assertExactJson(['error' => "El parámetro 'tipo_persona' es obligatorio."]);
    }

    public function test_rechaza_lista_con_query_param_invalido(): void
    {
        $response = $this->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=X');
        $response->assertStatus(400)
            ->assertExactJson(['error' => "El parámetro 'tipo_persona' es obligatorio."]);
    }

    public function test_puede_listar_naturales(): void
    {
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'DNI', 'nombre' => 'DNI Test', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false,
        ]);
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'RUC', 'nombre' => 'RUC Test', 'esta_activo' => true, 'es_para_natural' => false, 'es_para_juridica' => true,
        ]);

        $response = $this->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=N');
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

        $response = $this->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=J');
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
        $response = $this->deleteJson('/api/lf-itse/tipos-documento-identidad/'.$m->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tipos_documento_identidad', ['id' => $m->id]);
    }
}
