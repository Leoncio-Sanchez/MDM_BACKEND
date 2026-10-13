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

    private const URL = '/api/lf-itse/tipos-procedimiento-tupa';

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
        $this->getAuthUser(); // usuario_id tiene FK a auth_user
        DB::table('unidades_organicas')->insert([
            'id' => 1, 'codigo' => 'U01', 'nombre' => 'Unidad 1', 'esta_activo' => true,
        ]);
    }

    private function crearTipo(string $codigo = 'T01'): TipoProcedimientoTupaModel
    {
        return TipoProcedimientoTupaModel::create([
            'codigo' => $codigo, 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false,
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1,
        ]);
    }

    /** @param array<string, mixed> $cambios @return array<string, mixed> */
    private function datos(array $cambios = []): array
    {
        return [
            'codigo' => 'T02', 'nombre' => 'Tupa nuevo', 'plazo_atencion_dias' => 10,
            'dias_alerta_vencimiento' => 2, 'unidad_organica_id' => 1, ...$cambios,
        ];
    }

    private function autenticado(): self
    {
        return $this->actingAs($this->getAuthUser(), 'api');
    }

    public function test_rechaza_todas_las_operaciones_sin_auth(): void
    {
        $this->getAuthUser();
        $this->seedDeps();
        $m = $this->crearTipo();

        $this->getJson(self::URL)->assertUnauthorized();
        $this->getJson(self::URL . '/' . $m->id)->assertUnauthorized();
        $this->postJson(self::URL, $this->datos())->assertUnauthorized();
        $this->putJson(self::URL . '/' . $m->id, [])->assertUnauthorized();
        $this->deleteJson(self::URL . '/' . $m->id)->assertUnauthorized();

        $this->assertDatabaseHas('tipos_procedimiento_tupa', ['id' => $m->id]);
    }

    public function test_puede_listar_tipos(): void
    {
        $this->seedDeps();
        $this->crearTipo();

        $this->autenticado()->getJson(self::URL)->assertOk()->assertJsonCount(1);
    }

    public function test_puede_obtener_tipo(): void
    {
        $this->seedDeps();
        $m = $this->crearTipo();

        $this->autenticado()->getJson(self::URL . '/' . $m->id)->assertOk()->assertJsonPath('codigo', 'T01');
    }

    public function test_responde_404_si_no_existe(): void
    {
        $this->autenticado()->getJson(self::URL . '/999')->assertNotFound()->assertJsonStructure(['error']);
        $this->autenticado()->deleteJson(self::URL . '/999')->assertNotFound();
    }

    public function test_puede_crear_tipo_con_auth(): void
    {
        $this->seedDeps();

        $this->autenticado()->postJson(self::URL, $this->datos())->assertCreated();

        $this->assertDatabaseHas('tipos_procedimiento_tupa', ['codigo' => 'T02', 'usuario_id' => 1, 'esta_activo' => true]);
    }

    public function test_respeta_esta_activo_al_crear(): void
    {
        $this->seedDeps();

        $this->autenticado()->postJson(self::URL, $this->datos(['esta_activo' => false]))
            ->assertCreated()
            ->assertJsonPath('esta_activo', false);
    }

    public function test_rechaza_codigo_duplicado_aunque_venga_en_minusculas(): void
    {
        $this->seedDeps();
        $this->crearTipo('PE-01');

        $this->autenticado()->postJson(self::URL, $this->datos(['codigo' => ' pe-01 ']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo']);
    }

    public function test_rechaza_datos_invalidos_con_422(): void
    {
        $this->seedDeps();

        $this->autenticado()->postJson(self::URL, $this->datos([
            'codigo' => str_repeat('X', 31),
            'nombre' => str_repeat('N', 201),
            'unidad_organica_id' => 999,
            'plazo_atencion_dias' => 3,
            'dias_alerta_vencimiento' => 10,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo', 'nombre', 'unidad_organica_id', 'dias_alerta_vencimiento']);

        $this->assertDatabaseCount('tipos_procedimiento_tupa', 0);
    }

    public function test_puede_actualizar_tipo(): void
    {
        $this->seedDeps();
        $m = $this->crearTipo();

        $this->autenticado()->putJson(self::URL . '/' . $m->id, ['codigo' => 'T03', 'nombre' => 'Tupa Upd'])->assertOk();

        $this->assertDatabaseHas('tipos_procedimiento_tupa', ['id' => $m->id, 'codigo' => 'T03']);
    }

    public function test_actualizar_permite_conservar_su_propio_codigo(): void
    {
        $this->seedDeps();
        $m = $this->crearTipo('T01');

        $this->autenticado()->putJson(self::URL . '/' . $m->id, ['codigo' => 'T01', 'nombre' => 'Mismo código'])->assertOk();
    }

    public function test_puede_eliminar_tipo_sin_expedientes(): void
    {
        $this->seedDeps();
        $m = $this->crearTipo();

        $this->autenticado()->deleteJson(self::URL . '/' . $m->id)->assertNoContent();

        $this->assertDatabaseMissing('tipos_procedimiento_tupa', ['id' => $m->id]);
    }

    public function test_no_elimina_un_tipo_usado_por_expedientes(): void
    {
        $this->seedDeps();
        $m = $this->crearTipo();
        DB::table('expedientes')->insert(['tipo_procedimiento_tupa_id' => $m->id]);

        $this->autenticado()->deleteJson(self::URL . '/' . $m->id)
            ->assertStatus(409)
            ->assertJsonStructure(['error']);

        $this->assertDatabaseHas('tipos_procedimiento_tupa', ['id' => $m->id]);
    }

    public function test_monto_no_aparece_en_json(): void
    {
        $this->seedDeps();
        $this->crearTipo();

        $response = $this->autenticado()->getJson(self::URL)->assertOk();
        $this->assertArrayNotHasKey('monto', $response->json()[0]);
    }
}
