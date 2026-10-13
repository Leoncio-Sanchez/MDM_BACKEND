<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Zonificacion\Infrastructure\Persistence\Models\ZonificacionModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ZonificacionTest extends TestCase {
    use RefreshDatabase;

    private function getAuthUser(): User {
        $id = DB::table('auth_user')->insertGetId([
            'username' => 'test_user',
            'password' => 'secret',
            'is_active' => true,
        ]);
        return User::find($id);
    }

    public function test_puede_listar_zonificaciones(): void {
        ZonificacionModel::create([
            'codigo' => 'TEST1', 
            'nombre' => 'Test 1', 
            'esta_activo' => true, 
            'fecha_digitacion' => '2026-04-14 13:41:31', 
            'usuario_id' => 1
        ]);
        
        $response = $this->comoUsuarioApi()->getJson('/api/lf-itse/zonificaciones');
        
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_puede_crear_zonificacion(): void {
        $data = ['codigo' => 'TEST2', 'nombre' => 'Test 2', 'esta_activo' => true];
        
        $response = $this->actingAs($this->getAuthUser(), 'api')->postJson('/api/lf-itse/zonificaciones', $data);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('zonificaciones', ['codigo' => 'TEST2']);
    }

    public function test_puede_actualizar_zonificacion(): void {
        $m = ZonificacionModel::create([
            'codigo' => 'TEST1', 
            'nombre' => 'Test 1', 
            'esta_activo' => true, 
            'fecha_digitacion' => '2026-04-14 13:41:31', 
            'usuario_id' => 1
        ]);
        $data = ['codigo' => 'TEST1', 'nombre' => 'Test Updated', 'esta_activo' => false];
        
        $response = $this->actingAs($this->getAuthUser(), 'api')->putJson('/api/lf-itse/zonificaciones/' . $m->id, $data);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('zonificaciones', ['nombre' => 'Test Updated']);
    }

    public function test_puede_eliminar_zonificacion(): void {
        $m = ZonificacionModel::create([
            'codigo' => 'TEST1', 
            'nombre' => 'Test 1', 
            'esta_activo' => true, 
            'fecha_digitacion' => '2026-04-14 13:41:31', 
            'usuario_id' => 1
        ]);
        
        $response = $this->comoUsuarioApi()->deleteJson('/api/lf-itse/zonificaciones/' . $m->id);
        
        $response->assertStatus(204);
        $this->assertDatabaseMissing('zonificaciones', ['id' => $m->id]);
    }

    public function test_rechaza_todas_las_operaciones_sin_auth(): void
    {
        $url = '/api/lf-itse/zonificaciones';

        $this->getJson($url)->assertUnauthorized();
        $this->getJson($url . '/1')->assertUnauthorized();
        $this->postJson($url, [])->assertUnauthorized();
        $this->putJson($url . '/1', [])->assertUnauthorized();
        $this->deleteJson($url . '/1')->assertUnauthorized();
    }
}
