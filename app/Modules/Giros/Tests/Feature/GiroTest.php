<?php
declare(strict_types=1);
namespace App\Modules\Giros\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Giros\Infrastructure\Persistence\Models\GiroModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GiroTest extends TestCase {
    use RefreshDatabase;

    private function getAuthUser(): User {
        /** @var User|null $existing */
        $existing = User::where('username', 'test_user')->first();
        if ($existing) return $existing;

        $id = DB::table('auth_user')->insertGetId(\Tests\Fixtures\UsuarioAuth::datos([
            'is_staff' => true,
            'is_superuser' => true, // todos los accesos del perfil
            'username' => 'test_user',
            'password' => 'secret',
            'is_active' => true,
        ]));
        /** @var User $user */
        $user = User::find($id);
        return $user;
    }

    public function test_puede_listar_giros(): void {
        $this->getAuthUser();
        GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        
        $response = $this->comoUsuarioApi()->getJson('/api/lf-itse/giros');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_puede_obtener_giro(): void {
        $this->getAuthUser();
        $m = GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        $response = $this->comoUsuarioApi()->getJson('/api/lf-itse/giros/' . $m->id);
        $response->assertStatus(200);
        $this->assertEquals(1234, $response->json('ciiu_id'));
    }

    public function test_rechaza_crear_giro_sin_auth(): void {
        $data = ['ciiu_id' => 5678, 'nombre' => 'Giro auth', 'esta_activo' => true];
        $response = $this->postJson('/api/lf-itse/giros', $data);
        $response->assertStatus(401);
    }

    public function test_puede_crear_giro_con_auth(): void {
        $data = ['ciiu_id' => 5678, 'nombre' => 'Giro auth', 'esta_activo' => true];
        $response = $this->actingAs($this->getAuthUser(), 'api')->postJson('/api/lf-itse/giros', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('giros', ['ciiu_id' => 5678, 'usuario_id' => $this->getAuthUser()->id]);
    }

    public function test_puede_actualizar_giro(): void {
        $this->getAuthUser();
        $m = GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        $data = ['ciiu_id' => 9999, 'nombre' => 'Giro Upd', 'esta_activo' => false];
        $response = $this->actingAs($this->getAuthUser(), 'api')->putJson('/api/lf-itse/giros/' . $m->id, $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('giros', ['ciiu_id' => 9999]);
    }

    public function test_puede_eliminar_giro(): void {
        $this->getAuthUser();
        $m = GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        $response = $this->comoUsuarioApi()->deleteJson('/api/lf-itse/giros/' . $m->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('giros', ['id' => $m->id]);
    }

    public function test_rechaza_actualizar_sin_auth(): void {
        $this->getAuthUser();
        $m = GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        $response = $this->putJson('/api/lf-itse/giros/' . $m->id, []);
        $response->assertStatus(401);
    }

    public function test_rechaza_todas_las_operaciones_sin_auth(): void
    {
        $url = '/api/lf-itse/giros';

        $this->getJson($url)->assertUnauthorized();
        $this->getJson($url . '/1')->assertUnauthorized();
        $this->postJson($url, [])->assertUnauthorized();
        $this->putJson($url . '/1', [])->assertUnauthorized();
        $this->deleteJson($url . '/1')->assertUnauthorized();
    }
}
