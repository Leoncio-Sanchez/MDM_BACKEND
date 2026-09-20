<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Tests\Feature;

use App\Models\User;
use App\Modules\TipoProcedimientoTupa\Infrastructure\Persistence\Models\TipoProcedimientoTupaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TipoProcedimientoTupaTest extends TestCase
{
    use RefreshDatabase;

    private function getAuthUser(): User
    {
        /** @var User|null $existing */
        $existing = User::where('username', 'test_user')->first();
        if ($existing) {
            return $existing;
        }

        $id = DB::table('auth_user')->insertGetId([
            'username' => 'test_user',
            'password' => 'secret',
            'is_active' => true,
        ]);
        /** @var User $user */
        $user = User::find($id);

        return $user;
    }

    private function seedDeps(): void
    {
        DB::table('unidades_organicas')->insert([
            'id' => 1, 'codigo' => 'U01', 'nombre' => 'Unidad 1', 'esta_activo' => true,
        ]);
    }

    public function test_puede_listar_tipos(): void
    {
        $this->getAuthUser();
        $this->seedDeps();
        TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1,
        ]);

        $response = $this->getJson('/api/lf-itse/tipos-procedimiento-tupa');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_puede_obtener_tipo(): void
    {
        $this->getAuthUser();
        $this->seedDeps();
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1,
        ]);
        $response = $this->getJson('/api/lf-itse/tipos-procedimiento-tupa/'.$m->id);
        $response->assertStatus(200);
        $this->assertEquals('T01', $response->json('codigo'));
    }

    public function test_rechaza_crear_tipo_sin_auth(): void
    {
        $data = [
            'codigo' => 'T02', 'nombre' => 'Tupa auth', 'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 2, 'unidad_organica_id' => 1,
        ];
        $response = $this->postJson('/api/lf-itse/tipos-procedimiento-tupa', $data);
        $response->assertStatus(401);
    }

    public function test_puede_crear_tipo_con_auth(): void
    {
        $this->seedDeps();
        $data = [
            'codigo' => 'T02', 'nombre' => 'Tupa auth', 'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 2, 'unidad_organica_id' => 1,
        ];
        $response = $this->actingAs($this->getAuthUser(), 'api')->postJson('/api/lf-itse/tipos-procedimiento-tupa', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('tipos_procedimiento_tupa', ['codigo' => 'T02', 'usuario_id' => 1]);
    }

    public function test_puede_actualizar_tipo(): void
    {
        $this->getAuthUser();
        $this->seedDeps();
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1,
        ]);
        $data = ['codigo' => 'T03', 'nombre' => 'Tupa Upd'];
        $response = $this->actingAs($this->getAuthUser(), 'api')->putJson('/api/lf-itse/tipos-procedimiento-tupa/'.$m->id, $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('tipos_procedimiento_tupa', ['codigo' => 'T03']);
    }

    public function test_puede_eliminar_tipo(): void
    {
        $this->getAuthUser();
        $this->seedDeps();
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1,
        ]);
        $response = $this->deleteJson('/api/lf-itse/tipos-procedimiento-tupa/'.$m->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tipos_procedimiento_tupa', ['id' => $m->id]);
    }

    public function test_rechaza_actualizar_sin_auth(): void
    {
        $this->getAuthUser();
        $this->seedDeps();
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1,
        ]);
        $response = $this->putJson('/api/lf-itse/tipos-procedimiento-tupa/'.$m->id, []);
        $response->assertStatus(401);
    }

    public function test_monto_no_aparece_en_json(): void
    {
        $this->getAuthUser();
        $this->seedDeps();
        TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1,
        ]);
        $response = $this->getJson('/api/lf-itse/tipos-procedimiento-tupa');
        $response->assertStatus(200);
        $this->assertArrayNotHasKey('monto', $response->json()[0]);
    }
}
