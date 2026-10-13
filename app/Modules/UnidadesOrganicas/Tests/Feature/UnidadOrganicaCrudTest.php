<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UnidadOrganicaCrudTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/lf-itse/unidades-organicas';

    private function crearUnidad(string $codigo = 'SGLC', string $nombre = 'SUBGERENCIA DE LICENCIAS'): int
    {
        return DB::table('unidades_organicas')->insertGetId(\Tests\Fixtures\Registros::unidadOrganica([
            'codigo' => $codigo, 'nombre' => $nombre, 'sigla' => $codigo, 'esta_activo' => true,
        ]));
    }

    public function test_rechaza_escritura_y_detalle_sin_auth(): void
    {
        $id = $this->crearUnidad();

        $this->getJson(self::URL . "/{$id}")->assertUnauthorized();
        $this->postJson(self::URL, ['codigo' => 'X', 'nombre' => 'X'])->assertUnauthorized();
        $this->putJson(self::URL . "/{$id}", ['codigo' => 'X', 'nombre' => 'X'])->assertUnauthorized();
        $this->deleteJson(self::URL . "/{$id}")->assertUnauthorized();

        $this->assertDatabaseHas('unidades_organicas', ['id' => $id, 'codigo' => 'SGLC']);
    }

    public function test_crea_una_unidad_normalizando_codigo_y_sigla(): void
    {
        $this->comoUsuarioApi()
            ->postJson(self::URL, ['codigo' => ' sgdel ', 'nombre' => ' Subgerencia de Desarrollo Económico ', 'sigla' => 'sgdel'])
            ->assertCreated()
            ->assertJsonPath('codigo', 'SGDEL')
            ->assertJsonPath('nombre', 'Subgerencia de Desarrollo Económico')
            ->assertJsonPath('sigla', 'SGDEL')
            ->assertJsonPath('esta_activo', true);
    }

    public function test_sigla_opcional_se_guarda_como_cadena_vacia(): void
    {
        $this->comoUsuarioApi()
            ->postJson(self::URL, ['codigo' => 'GM', 'nombre' => 'GERENCIA MUNICIPAL', 'esta_activo' => false])
            ->assertCreated()
            ->assertJsonPath('sigla', '')
            ->assertJsonPath('esta_activo', false);
    }

    public function test_rechaza_codigo_duplicado_aunque_venga_en_minusculas(): void
    {
        $this->crearUnidad('SGLC');

        $this->comoUsuarioApi()
            ->postJson(self::URL, ['codigo' => 'sglc', 'nombre' => 'OTRA'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.codigo.0', 'Ya existe una unidad orgánica con ese código.');
    }

    public function test_valida_obligatorios_y_limites_de_columnas(): void
    {
        $this->comoUsuarioApi()->postJson(self::URL, [])->assertUnprocessable()->assertJsonValidationErrors(['codigo', 'nombre']);

        $this->comoUsuarioApi()
            ->postJson(self::URL, ['codigo' => str_repeat('C', 21), 'nombre' => str_repeat('N', 101), 'sigla' => str_repeat('S', 21)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo', 'nombre', 'sigla']);

        $this->assertDatabaseCount('unidades_organicas', 0);
    }

    public function test_obtiene_una_unidad_o_responde_404(): void
    {
        $id = $this->crearUnidad();

        $this->comoUsuarioApi()->getJson(self::URL . "/{$id}")->assertOk()->assertJsonPath('codigo', 'SGLC');
        $this->comoUsuarioApi()->getJson(self::URL . '/999')->assertNotFound()->assertJsonStructure(['error']);
    }

    public function test_actualiza_y_permite_conservar_su_propio_codigo(): void
    {
        $id = $this->crearUnidad('SGLC');

        $this->comoUsuarioApi()
            ->putJson(self::URL . "/{$id}", ['codigo' => 'SGLC', 'nombre' => 'NUEVO NOMBRE', 'sigla' => 'SGLC', 'esta_activo' => false])
            ->assertOk()
            ->assertJsonPath('nombre', 'NUEVO NOMBRE')
            ->assertJsonPath('esta_activo', false);
    }

    public function test_no_permite_tomar_el_codigo_de_otra_unidad(): void
    {
        $this->crearUnidad('SGLC');
        $otra = $this->crearUnidad('GM', 'GERENCIA MUNICIPAL');

        $this->comoUsuarioApi()
            ->putJson(self::URL . "/{$otra}", ['codigo' => 'SGLC', 'nombre' => 'GERENCIA MUNICIPAL'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo']);
    }

    public function test_actualizar_inexistente_responde_404(): void
    {
        $this->comoUsuarioApi()->putJson(self::URL . '/999', ['codigo' => 'X', 'nombre' => 'X'])->assertNotFound();
    }

    public function test_elimina_una_unidad_sin_dependencias(): void
    {
        $id = $this->crearUnidad();

        $this->comoUsuarioApi()->deleteJson(self::URL . "/{$id}")->assertNoContent();

        $this->assertDatabaseMissing('unidades_organicas', ['id' => $id]);
    }

    public function test_no_elimina_una_unidad_con_procedimientos_tupa(): void
    {
        $id = $this->crearUnidad();
        DB::table('tipos_procedimiento_tupa')->insert(\Tests\Fixtures\Registros::tipoProcedimientoTupa([
            'codigo' => 'PE-01', 'nombre' => 'X', 'plazo_atencion_dias' => 5, 'dias_alerta_vencimiento' => 1,
            'fecha_digitacion' => now(), 'usuario_id' => $this->usuarioApi()->id, 'unidad_organica_id' => $id,
        ]));

        $this->comoUsuarioApi()->deleteJson(self::URL . "/{$id}")
            ->assertStatus(409)
            ->assertJsonPath('error', fn (string $msg) => str_contains($msg, '1 procedimiento(s) TUPA'));

        $this->assertDatabaseHas('unidades_organicas', ['id' => $id]);
    }

    public function test_eliminar_inexistente_responde_404(): void
    {
        $this->comoUsuarioApi()->deleteJson(self::URL . '/999')->assertNotFound();
    }
}
